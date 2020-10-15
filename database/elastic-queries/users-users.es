
GET /users/_cache/clear

GET /users/users/_search
{
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "should": [
                        {
                            "wildcard": {
                                "first_name": "**"
                            }
                        },
                        {
                            "wildcard": {
                                "last_name": "**"
                            }
                        },
                        {
                            "wildcard": {
                                "middle_name": "**"
                            }
                        },
                        {
                            "wildcard": {
                                "login": "**"
                            }
                        }
                    ],
                    "must": {
                        "bool": {
                            "should": {//Доступ
                                "terms": {
                                    "organization_id": [
                                        2,
                                        3,
                                        67,
                                        125
                                    ]
                                }
                            },
                            "must": [
                                { //Фильтр
                                    "terms": {
                                        "organization_id": [
                                            3
                                        ]
                                    }
                                }
                                // ,
                                // { //Фильтр
                                //     "terms": {
                                //         "is_work": [
                                //             0
                                //         ]
                                //     }
                                // }
                            ]
                        }
                    }
                }
            }
        }
    }
}