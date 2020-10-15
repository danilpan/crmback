// 1 По проектам и организациям  - с фильтром по ID и категории проекта
GET /projects/projects/_search
{
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "should": [ // полнотекстный поиск
                        {
                            "term": {
                                "item_id": "детские товары"
                            }
                        },
                        {
                            "wildcard": {
                                "title": "детски*"
                            }
                        },
                        {
                            "wildcard": {
                                "description": "*детские товары*"
                            } 
                        },
                        {
                            "wildcard": {
                                "project_page.link": "*детские товары*"
                            }
                        },
                        {
                            "wildcard": {
                                "project_page.phones.phone": "*детские товары*"
                            }
                        }
                    ],
                    "must": { // доступ
                        "bool": {
                            "should": [
                                {
                                    "terms": {
                                        "organization_id": [
                                            67,
                                            125
                                        ]
                                    }
                                },
                                {
                                    "terms": {
                                        "id": [
                                            1069,
                                            1993
                                        ]
                                    }
                                }
                            ]
                            // ,
                            // "must": [ //фильтр
                            //     // {
                            //     //     "terms": {
                            //     //         "id": [
                            //     //             1001,
                            //     //             1003
                            //     //         ]
                            //     //     }
                            //     // },
                            //     {
                            //         "terms": {
                            //             "category_id": [
                            //                 6,5
                            //             ]
                            //         }
                            //     }
                            // ]
                        }
                    }
                }
            }
        }
    }
}

// 2 По организациям и за вычетом проектов - с фильтрами по ID и категории проекта
GET /projects/projects/_search
{
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "should": [
                        {
                            "term": {
                                "item_id": ""
                            }
                        },
                        {
                            "wildcard": {
                                "title": "**"
                            }
                        },
                        {
                            "term": {
                                "description": ""
                            }
                        },
                        {
                            "wildcard": {
                                "project_page.link": "**"
                            }
                        },
                        {
                            "wildcard": {
                                "project_page.phones.phone": "**"
                            }
                        }
                    ]
                    // ,"must": {
                    //     "bool": {
                    //         "must": [
                    //             {
                    //                 "terms": {
                    //                     "organization_id": [
                    //                         67,
                    //                         125
                    //                     ]
                    //                 }
                    //             },
                    //             {
                    //                 "terms": {
                    //                     "id": [
                    //                         1001,
                    //                         1003
                    //                     ]
                    //                 }
                    //             },
                    //             {
                    //                 "terms": {
                    //                     "category_id": [
                    //                         6
                    //                     ]
                    //                 }
                    //             }
                    //         ],
                    //         "must_not": [
                    //             {
                    //                 "terms": {
                    //                     "id": [
                    //                         1249,
                    //                         1921
                    //                     ]
                    //                 }
                    //             }
                    //         ]
                    //     }
                    // }
                }
            }
        }
    },
    "aggs": {
        "dt": {
            "date_histogram": {
                "field": "created_at",
                "interval": "day"
            },
            "aggs": {
                "id": {
                    "terms": {
                        "field": "unique_identifier"
                    }
                }
            }
        }
    }
}

// 3 За вычетом организаций кроме указанных проектов - с фильтрами по ID и категории проекта
GET /projects/projects/_search
{
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "should": [
                        {
                            "term": {
                                "item_id": ""
                            }
                        },
                        {
                            "wildcard": {
                                "title": "**"
                            }
                        },
                        {
                            "term": {
                                "description": ""
                            }
                        },
                        {
                            "wildcard": {
                                "project_page.link": "**"
                            }
                        },
                        {
                            "wildcard": {
                                "project_page.phones.phone": "**"
                            }
                        }
                    ],
                    "must": {
                        "bool": {
                            "should": [
                                {
                                    "bool": {
                                        "must": [
                                            {
                                                "terms": {
                                                    "id": [
                                                        2734,
                                                        624
                                                    ]
                                                }
                                            }
                                        ]
                                    }
                                },
                                {
                                    "bool": {
                                        "must_not": [
                                            {
                                                "terms": {
                                                    "organization_id": [
                                                        67,
                                                        125
                                                    ]
                                                }
                                            }
                                        ]
                                    }
                                }
                            ],
                            "must": [ //фильтр
                                {
                                    "terms": {
                                        "id": [
                                            2734
                                        ]
                                    }
                                },
                                {
                                    "terms": {
                                        "category_id": [
                                            0
                                        ]
                                    }
                                }
                            ]
                        }
                    }
                }
            }
        }
    }
}

//4 За вычетом организаций и за вычетом указанных проектов - с фильтрами по ID и категории проекта
GET /projects/projects/_search
{
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "should": [
                        {
                            "term": {
                                "item_id": ""
                            }
                        },
                        {
                            "wildcard": {
                                "title": "**"
                            }
                        },
                        {
                            "term": {
                                "description": ""
                            }
                        },
                        {
                            "wildcard": {
                                "project_page.link": "**"
                            }
                        },
                        {
                            "wildcard": {
                                "project_page.phones.phone": "**"
                            }
                        }
                    ],
                    "must_not": [
                        {
                            "terms": {
                                "organization_id": [
                                    67,
                                    125
                                ]
                            }
                        },
                        {
                            "terms": {
                                "id": [
                                    1069,
                                    2588
                                ]
                            }
                        }
                    ],
                    "must": [ //фильтр
                        {
                            "terms": {
                                "id": [
                                    14,22,25,29
                                ]
                            }
                        },
                        {
                            "terms": {
                                "category_id": [
                                    0
                                ]
                            }
                        }
                    ]
                }
            }
        }
    }
}

GET /projects/projects/_search
{
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "must": [
                        {
                            "wildcard": {
                                "title": "детски*"
                            }
                        }
                    ]
                }
            }
        }
    }
}

POST /projects/projects
{
    "settings": {
        "analysis": {
            "filter": {
                "russian_stop": {
                    "type": "stop",
                    "stopwords": "_russian_"
                },
                "russian_keywords": {
                    "type": "keyword_marker",
                    "keywords": [
                        "пример"
                    ]
                },
                "russian_stemmer": {
                    "type": "stemmer",
                    "language": "russian"
                }
            },
            "analyzer": {
                "russian": {
                    "tokenizer": "standard",
                    "filter": [
                        "lowercase",
                        "russian_stop",
                        "russian_keywords",
                        "russian_stemmer"
                    ]
                }
            }
        }
    }
}


GET /projects/projects/_search
{
    "query": {
        "match_all": {}
    },
    "aggs": {
        "dt": {
            "date_histogram": {
                "field": "created_at",
                "interval": "day"
            },
            "aggs": {
                "id": {
                    "terms": {
                        "field": "unique_identifier"
                    }
                }
            }
        }
    }
}