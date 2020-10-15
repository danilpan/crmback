// Поиск по родителю. С ограничениями.
GET /organizations/organizations/_search
{
    "query" : {
        "constant_score" :{
            "filter": {
                "bool": {
                    "should": {
                        "terms" : {
                            "id" : [
                                2, 3,5,12
                            ]
                        }
                    },
                    "must": {
                        "terms" : {
                            "parent_id" : [ 2 ]
                        }
                    }
                }
            }
        }
    }
}