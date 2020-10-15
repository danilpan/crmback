GET /calls/calls/_search
{
    "query": {
        "match_all": {}
    },
    "aggs": {
        "dt": {
            "date_histogram": {
                "field": "time",
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