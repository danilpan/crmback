### Продукты

#### Получение списка категории продуктов
**GET:** */api/v2/product_category*   

Пример ответа:  
```
{
    "total": 2,
    "data": [
        {
            "id": 1,
            "name": "Category 2",
            "organization_id": 1,
            "is_work": 1
        },
        {
            "id": 2,
            "name": "Category 2",
            "organization_id": 1,
            "is_work": 1
        }
    ]
}
```

#### Получение категории по ID:
**GET:** */api/v2/product_category/{id}*  

Параметры:
- **id**: *обязательный*, ID категории. 

Пример ответа:  
```
{
    "id": 1,
    "name": "Category 1",
    "organization_id": 1,
    "is_work": 1
}
```

#### Редактирование категории:
**PUT:** */api/v2/product_category/{id}*

Параметры:
- **id**: *обязательный*, ID категории.

Ограничение по полям:   
- **name**: *необязательный*, название категории.
- **organization_id**: *необязательный*, Id-организации.
- **is_work**: *необязательный*, включен ли.
   
Пример запроса:
```
{
    "id": 16,
    "name": "Category-16",
    "organization_id": 1,
    "is_work": 1
}
```  

Пример ответа:  
```
{
    "id": 16,
    "name": "Category 16",
    "organization_id": 2,
    "is_work": 1
}
```

#### Создание категории:
**POST:** */api/v2/product_category*

Ограничение по полям:   
- **name**: *обязательный*, название категории.
- **organization_id**: *необязательный*, Id-организации.
- **is_work**: *необязательный*, включен ли.

Все остальные поля являются необязательными.
   
Пример запроса:
```
{
	"name": "Category 5",
	"organization_id": 2,
	"is_work": 1
}
```  

Пример ответа:  
```
{
    "id": 5,
    "name": "Category 5",
    "organization_id": 2,
    "is_work": 1
}
```

#### Удаление категории:
**DELETE:** */api/v2/product_category/{id}*

Параметры:
- **id**: *обязательный*, ID категории.
   
Пример запроса:
```
Method: Delete.
Url: http://crmka.lcl/api/v2/product_category/5
```  

Пример ответа:  
```
1
```