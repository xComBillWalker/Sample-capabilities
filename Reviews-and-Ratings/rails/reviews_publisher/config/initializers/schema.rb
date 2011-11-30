require 'avro'
REVIEW_PROTOCOL_JSON = <<-EOS
{
  "protocol" : "Reviews",
  "namespace" : "com.x.product.reviews",
  "types" : [ {
    "type" : "enum",
    "name" : "StandardProductIdType",
    "symbols" : [ "UPC", "SKU", "EAN", "MPN", "ASIN", "ISBN" ]
  }, {
    "type" : "record",
    "name" : "StandardProductId",
    "fields" : [ {
      "name" : "type",
      "type" : "StandardProductIdType"
    }, {
      "name" : "value",
      "type" : "string"
    } ]
  }, {
    "type" : "record",
    "name" : "IndividualReview",
    "fields" : [ {
      "name" : "review",
      "type" : "string"
    }, {
      "name" : "by",
      "type" : "string"
    }, {
      "name" : "date",
      "type" : "string"
    }, {
      "name" : "rating",
      "type" : [ "null", "int" ]
    } ]
  }, {
    "type" : "record",
    "name" : "ReviewCollection",
    "fields" : [ {
      "name" : "ratingCount",
      "type" : "int"
    }, {
      "name" : "average",
      "type" : "float"
    }, {
      "name" : "bestRating",
      "type" : [ "null", "int" ]
    }, {
      "name" : "worstRating",
      "type" : [ "null", "int" ]
    }, {
      "name" : "collection",
      "type" : {
        "type" : "array",
        "items" : "IndividualReview"
      }
    } ]
  }, {
    "type" : "record",
    "name" : "ProductDetails",
    "fields" : [ {
      "name" : "productId",
      "type" : "StandardProductId"
    }, {
      "name" : "reviews",
      "type" : "ReviewCollection"
    } ]
  }, {
    "type" : "record",
    "name" : "GetProductReviewsMessage",
    "fields" : [ {
      "name" : "product_id",
      "type" : "string"
    } ],
    "topic" : "/product/reviews/find"
  }, {
    "type" : "record",
    "name" : "ProductReviewsMessage",
    "fields" : [ {
      "name" : "reviews",
      "type" : "ReviewCollection"
    } ],
    "topic" : "/product/reviews/find/success"
  }, {
    "type" : "record",
    "name" : "GetProductReviewsCollectionMessage",
    "fields" : [ {
      "name" : "query",
      "type" : "string"
    } ],
    "topic" : "/product/reviews/search"
  }, {
    "type" : "record",
    "name" : "ProductReviewsCollectionMessage",
    "fields" : [ {
      "name" : "Results",
      "type" : {
        "type" : "array",
        "items" : "ReviewCollection"
      }
    } ],
    "topic" : "/product/reviews/search/success"
  }, {
    "type" : "record",
    "name" : "UpdateProductReview",
    "fields" : [ {
      "name" : "code",
      "type" : "int"
    }, {
      "name" : "message",
      "type" : "string"
    } ],
    "topic" : "/product/reviews/update/success"
  } ],
  "messages" : {
  }
}
EOS
SCHEMA = Avro::Protocol.parse(REVIEW_PROTOCOL_JSON)