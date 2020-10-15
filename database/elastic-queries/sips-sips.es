// Выборка транков по организациям
GET /sips/sips/_search
{
    "query": {
    	"constant_score": {
    		"filter": {
		        "bool": {
		            "should": {
		                "terms": {
		                    "ats_group.organizations.id":[67,125]
		                }
		            }
		        }
    		}
    	}
    }
}