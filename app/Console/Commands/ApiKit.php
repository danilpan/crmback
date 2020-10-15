<?php
/**
 * Artisan команда, которая создаёт модель, контроллер, реквест, репозиторий,
 * сервис и контроллер специализированные для Crmka.pro.
 * Файлы создаются из шаблонов, хранящихся в каталоге ApiKit
 * В шаблоне паттерн {{NAME}} заменяется на имя в КемелКейс
 *                   {{name}} заменяется на имя в кебаб_кейс
 *                   {{table}} заменяется на имя в кебаб_кейс с добавлением "s" (название таблицы в БД)
 * @author Алексей Бабиев aka axsmak
 * @package Artisan
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApiKit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "api:kit
                                    {name : Имя модели в КэмелКейс. Например: MyModel},
                                    {--rewrite : Перезаписывать ли существующие файлы},
                                    {--m|model : Создать модель},
                                    {--c|controller : Создать контроллер},
                                    {--u|request : Создать реквест},
                                    {--r|resource : Создать ресурс},
                                    {--p|repository : Создать репозиторий},
                                    {--s|service : Создать сервис},
                                    {--g|migration : Создать миграцию},
                                    {--a|all : Создать всё. Такой же эффект будет, если не указывать никаких опций}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Создаёт модель, миграцию, реквест, репозиторий, сервис и контроллер специализированные для Crmka.pro.";

    /**
     * Коды цветов для раскраски вывода в консоль
     *
     * @var Array
     */
    private $colors = [
        "red" => "0;31",
        "brown" => "0;33",
        "green" => "0;32",
    ];

    /**
     * Пути к создаваемым файлам
     *
     * @var Array
     */
    private $destinations = [
        "Resource"   => "/../../Http/Resources/V2/{{NAME}}Resource.php",
        "Service"    => "/../../Services/{{NAME}}Service.php",
        "Repository" => "/../../Repositories/{{NAME}}Repository.php",
        "Model"      => "/../../Models/{{NAME}}.php",
        "Request"    => "/../../Http/Requests/Api/V2/{{NAME}}Request.php",
        "Controller" => "/../../Http/Controllers/Api/V2/{{NAME}}Controller.php"
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->rewrite = $this->option("rewrite");
        $this->api_name = $this->argument("name");
        $this->table_name = $this->camelToSnake($this->api_name);
        $this->item_name = $this->camelToSnake($this->api_name, $with_s = false);

        $m = $this->option("model");
        $c = $this->option("controller");
        $q = $this->option("request");
        $r = $this->option("resource");
        $p = $this->option("repository");
        $s = $this->option("service");
        $g = $this->option("migration");
        $a = $this->option("all");

        if (!$m && !$c && !$q && !$r && !$p && !$s && !$g) $a = true;

        if ($m || $a) $this->makeUnit("Model");
        if ($r || $a) $this->makeUnit("Resource");
        if ($q || $a) $this->makeUnit("Request");
        if ($p || $a) $this->makeUnit("Repository");
        if ($s || $a) $this->makeUnit("Service");
        if ($c || $a) $this->makeUnit("Controller");
    }

    /**
     * Переводит имя написанное в стиле КэмелКейс в стиль кебаб_кейс
     *
     * @param  string  $input Имя в стиле КэмелКейс
     * @param  boolean $with_s Дописывать ли в конце "s", если последний символ не "s"
     * @return string Имя в стиле кебаб_кейс
     */
    private function camelToSnake($input, $with_s = true)
    {
        preg_match_all("!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!", $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        $result = implode("_", $ret);
        return $result[strlen($result)-1] == "s" || !$with_s ? $result : $result."s";
    }

    /**
     * Подготавливает строку к цветному выводу в консоль
     *
     * @param  string $text Строка, которую надо раскрасить
     * @param  string $color Код цвета. Коды хранятся в массиве $this->colors[]
     * @return string Строка с кодом цвета
     */
    private function colored($text, $color = "1;37")
    {
        return "\033[".$color."m$text\033[0m";
    }

    /**
     * Создаёт файл из шаблона
     *
     * @param  string $src  Путь к шаблону относительно текущего каталога
     * @param  string $dest Путь к создаваемому файлу относительно текущего каталога
     * @param  string $unit Тип файла, модель, сервис, контроллер и т. д.
     * @return boolean Был ли создан файл
     */
    private function template($src, $dest, $unit)
    {
        if (file_exists($dest) && !$this->rewrite) {
            echo $this->colored("$unit ".str_replace(getcwd()."/", "",realpath($dest))." already exists\n", $this->colors["red"]);
            return false;
        }
        if (!file_exists($src)) {
            echo $this->colored("Template ".str_replace(getcwd()."/", "",realpath($dest))." not found\n", $this->colors["red"]);
            return false;
        }
        $data = file($src);
        $data = array_map(function($data) {
            $data = str_replace("{{NAME}}", $this->api_name, $data);
            $data = str_replace("{{table}}", $this->table_name, $data);
            $data = str_replace("{{item}}", $this->item_name, $data);
            return str_replace("{{name}}", strtolower($this->api_name), $data);
        }, $data);
        file_put_contents($dest, $data);
        echo $this->colored("Created $unit: ", $this->colors["green"]).str_replace(getcwd()."/", "",realpath($dest))."\n";
        return true;
    }

    /**
     * Подготавливает пути и создаёт файл из шаблона
     *
     * @param  string $unit Тип файла, модель, сервис, контроллер и т. д.
     * @return void
     */
    private function makeUnit($unit)
    {
        $dir = dirname( __FILE__ );
        $src = "$dir/ApiKit/__$unit";
        $dst = $dir.str_replace("{{NAME}}", $this->api_name, $this->destinations[$unit]);
        $success = $this->template($src, $dst, $unit);
        if ($success && $unit == "Model") {
            $this->call("make:migration", ["name" => "create_".$this->table_name."_table"]);
        };
    }
}
