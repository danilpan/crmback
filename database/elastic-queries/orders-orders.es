GET /orders/orders/_search
{
    "from": 0,
    "size": 1000,
    "_source" : ["key", "id","import_id","type","api_key", "projects"],
    "sort": [
        {
            "key": "asc"
        },
        {
            "created_at": "asc"
        }
    ],
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "must": [
                        {
                            "term": {
                                "created_at_year": "2018"
                            }
                        }
                    ]
                }
            }
        }
    }
}

GET /orders/orders/_search
{
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "must": [
                        {
                            "range": {
                                "created_at": {
                                    "gte": "2018-04-12",
                                    "lte": "2018-04-12"
                                }
                            }
                        }
                    ]
                }
            }
        }
    }
}


GET /orders/orders/_search?size=0
{
    "query": {
        "match_all": {}
    },
    "aggs": {
        "group": {
            "terms": {
                "field": "projects.title"
            },
            "aggs": {
                "group": {
                    "terms": {
                        "field": "current_1_group_status_id"
                    }
                },
                "upsale": {
                    "terms": {
                        "field": "sales.upsale"
                    }
                },
                "priceAvg": {
                    "avg": {
                        "field": "sales.price"
                    }
                }
            }
        }
    }
}


GET /orders/orders/_search?size=0
{
    "query": {
        "match_all": {}
    },
    "aggs": {
        "group": {
            "terms": {
                "field": "projects.title"
            },
            "aggs": {
                "status": {
                    "terms": {
                        "field": "statuses.id"
                    },
                    "aggs": {
                        "summa": {
                            "sum": {
                                "field": "sales.price"
                            }
                        },
                        "avg": {
                            "avg": {
                                "field": "sales.price"
                            }
                        }
                    }
                },
                "upsale": {
                    "terms": {
                        "field": "sales.upsale"
                    },
                    "aggs": {
                        "summa": {
                            "sum": {
                                "field": "sales.price"
                            }
                        }
                    }
                },
                "in_transit": {
                    "terms": {
                        "field": "goods_in_transit"
                    }
                }
            }
        }
    }
}

GET /orders/orders/_search?size=0
{
    "query": {
        "match_all": {}
    },
    "aggs": {
        "group": {
            "date_histogram": {
                "field": "created_at",
                "interval": "day"
            },
            "aggs": {
                "group": {
                    "terms": {
                        "field": "current_1_group_status_id"
                    }
                }
            }
        }
    }
}

GET /orders/orders/_search
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

GET /orders/orders/_search
{
    "from": 0,
    "size": 100,
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "must": {
                        "bool": {
                            "should": [
                                {
                                    "term": {
                                        "organizations.id": 67
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


POST /orders/orders/_search?size=0
{
    "query": {
        "constant_score": {
            "filter": {
                "bool": {
                    "must": {
                        "bool": {
                            "should": [
                                {
                                    "term": {
                                        "current_1_group_status_id": 17
                                    }
                                }
                            ]
                        }
                    }
                }
            }
        }
    },
    "aggs": {
        "sales_sum": {
            "date_histogram": {
                "field": "created_at",
                "interval": "day"
            },
            "aggs": {
                "totalSum": {
                    "sum": {
                        "field": "organization_id"
                    }
                }
            }
        },
        "sales_avg": {
            "date_histogram": {
                "field": "created_at",
                "interval": "day"
            },
            "aggs": {
                "avg_grade": {
                    "avg": {
                        "field": "organization_id"
                    }
                }
            }
        }
    }
}

POST /orders/orders/_search?size=0
{
    "aggs": {
        "items_over_time": {
            "date_histogram": {
                "field": "created_at",
                "interval": "month"
            }
        }
    }
}

POST /orders/orders/_search?size=0
{
    "aggs": {
        "items_over_time": {
            "date_histogram": {
                "field": "created_at",
                "interval": "month"
            },
            "aggs": {
                "totalSum": {
                    "sum": {
                        "field": "organization_id"
                    }
                },
                "totalAvg": {
                    "avg": {
                        "field": "organization_id"
                    }
                }
            }
        }
    }
}

POST /orders/orders/_search?size=0
{
    "aggs": {
        "items_over_time": {
            "date_histogram": {
                "field": "created_at",
                "interval": "month"
            },
            "aggs": {
                "group": {
                    "terms": {
                        "field": "geo.name_ru"
                    },
                    "aggs": {
                        "group": {
                            "terms": {
                                "field": "projects.title"
                            },
                            "aggs": {
                                "totalSum": {
                                    "sum": {
                                        "field": "organization_id"
                                    }
                                },
                                "totalAvg": {
                                    "avg": {
                                        "field": "organization_id"
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

POST /orders/orders/_search?size=0
{
    "aggs": {
        "group": {
            "terms": {
                "field": "geo.name_ru"
            },
            "aggs": {
                "group": {
                    "terms": {
                        "field": "projects.title"
                    },
                    "aggs": {
                        "group": {
                            "date_histogram": {
                                "field": "created_at",
                                "interval": "day"
                            },
                            "aggs": {
                                "totalSum": {
                                    "sum": {
                                        "field": "organization_id"
                                    }
                                },
                                "totalAvg": {
                                    "avg": {
                                        "field": "organization_id"
                                    }
                                }
                            }
                        },
                        "totalSum": {
                            "sum": {
                                "field": "organization_id"
                            }
                        },
                        "totalAvg": {
                            "avg": {
                                "field": "organization_id"
                            }
                        }
                    }
                },
                "totalSum": {
                    "sum": {
                        "field": "organization_id"
                    }
                },
                "totalAvg": {
                    "avg": {
                        "field": "organization_id"
                    }
                }
            }
        }
    }
}
            