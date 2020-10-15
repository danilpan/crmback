### Продукты

#### Получение списка продуктов
**GET:** */api/v2/products*   

Пример ответа:  
```
{
    "total": 2,
    "data": [
        {
            "id": 1,
            "name": "Super product 11",
            "code_product": "p1",
            "uniqued_import_id": "p2",
            "id_organization": 1,
            "cat_id": 1,
            "article": "product11",
            "img": "productImg1.png",
            "parent_project": "Products",
            "parent_site": "ProductSite",
            "price_cost": 11000,
            "price_online": 1500,
            "price_prime": 1800,
            "weight": "115",
            "desc": "Description Super product 11",
            "script": "super script 1",
            "basic_unit": "Product 1 unit",
            "nabor": 1,
            "service": 1,
            "complect": 2,
            "basic_unit_seat": "Product 1",
            "is_work": 1
        },
        {
            "id": 2,
            "name": "Super product 2",
            "code_product": "p2",
            "uniqued_import_id": "p3",
            "id_organization": 2,
            "cat_id": 2,
            "article": "product2",
            "img": "productImg2.png",
            "parent_project": "Products",
            "parent_site": "ProductSite",
            "price_cost": 1000,
            "price_online": 1500,
            "price_prime": 1800,
            "weight": "15",
            "desc": "Description Super product 2",
            "script": "super script 2",
            "basic_unit": "Product 2 unit",
            "nabor": 1,
            "service": 1,
            "complect": 2,
            "basic_unit_seat": "Product 2",
            "is_work": 1
        }
    ]
}
```

#### Получение продукта по ID:
**GET:** */api/v2/products/{id}*  

Параметры:
- **id**: *обязательный*, ID продукта. 

Пример ответа:  
```
{
    "id": 1,
    "name": "Super product 11",
    "code_product": "p1",
    "uniqued_import_id": "p2",
    "id_organization": 1,
    "cat_id": 1,
    "article": "product11",
    "img": "productImg1.png",
    "parent_project": "Products",
    "parent_site": "ProductSite",
    "price_cost": 11000,
    "price_online": 1500,
    "price_prime": 1800,
    "weight": "115",
    "desc": "Description Super product 11",
    "script": "super script 1",
    "basic_unit": "Product 1 unit",
    "nabor": 1,
    "service": 1,
    "complect": 2,
    "basic_unit_seat": "Product 1",
    "is_work": 1
}
```

#### Редактирование продукта:
**PUT:** */api/v2/products/{id}*

Параметры:
- **id**: *обязательный*, ID продукта.

Ограничение по полям:   
- **name**: *обязательный*, Название продукта.
- **article**: *обязательный*, Артикул продукта.
- **desc**: *обязательный*, Описание продукта.
- **weight**: *обязательный*, Вес продукта.
- **price_cost**: *обязательный*, Себестоимость продукта.

Все остальные поля являются необязательными.
   
Пример запроса:
```
{
    "name"        : "Product-1", 
    "article"     : "product1",
    "desc"        : "Product-1 content",
    "weight"      : 150,
    "price_cost"  : 2500
}
```  

Пример ответа:  
```
{
    "id": 1,
    "name": "Product-1",
    "code_product": "p1",
    "uniqued_import_id": "p2",
    "id_organization": 1,
    "cat_id": 1,
    "article": "product1",
    "img": "productImg1.png",
    "parent_project": "Products",
    "parent_site": "ProductSite",
    "price_cost": 2500,
    "price_online": 1500,
    "price_prime": 1800,
    "weight": "150",
    "desc": "Product-1 content",
    "script": "super script 1",
    "basic_unit": "Product 1 unit",
    "nabor": 1,
    "service": 1,
    "complect": 2,
    "basic_unit_seat": "Product 1",
    "is_work": 1
}
```

#### Создание продукта:
**POST:** */api/v2/products*

Ограничение по полям:   
- **name**: *обязательный*, Название продукта.
- **article**: *обязательный*, Артикул продукта.
- **desc**: *обязательный*, Описание продукта.
- **weight**: *обязательный*, Вес продукта.
- **price_cost**: *обязательный*, Себестоимость продукта.

Все остальные поля являются необязательными.
   
Пример запроса:
```
{
    "name"        : "Product-5", 
    "article"     : "product5",
    "desc"        : "Product-5 content",
    "weight"      : 150,
    "price_cost"  : 2500
}
```  

Пример ответа:  
```
{
    "id": 5,
    "name": "Product-5",
    "code_product": null,
    "uniqued_import_id": null,
    "id_organization": null,
    "cat_id": null,
    "article": "product5",
    "img": null,
    "parent_project": null,
    "parent_site": null,
    "price_cost": 2500,
    "price_online": null,
    "price_prime": null,
    "weight": 150,
    "desc": "Product-5 content",
    "script": null,
    "basic_unit": null,
    "nabor": null,
    "service": null,
    "complect": null,
    "basic_unit_seat": null,
    "is_work": null
}
```

#### Удаление продукта:
**DELETE:** */api/v2/products/{id}*

Параметры:
- **id**: *обязательный*, ID продукта.
   
Пример запроса:
```
Method: Delete.
Url: http://crmka.lcl/api/v2/products/5
```  

Пример ответа:  
```
1
```