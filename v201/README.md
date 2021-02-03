## Clients:

> ### ```api\v201\getAllRestaurantData.fetch.php``` ``POST``
>
> #### Required fields:
>   - **englishName**  
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": {
>       "foods": "[foods arr]",
>       "restaurantInfo": "[res info arr]" 
>     }
>   }
>   ```
<hr>


> ### ```api\v201\sendOrder.add.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **englishName**
>   - **orders** ```[{"id": 3, "number": 2},{"id": 24, "number": 1}]```
>   - **details**  ```{"general": "st1", "common": [{"foodName": "name1", "description":"desc1"}]}```
>   - **orderTable** (order table or address is require)
>   - **address**
>   - deliveryDate (not required)
>   - deliveryPrice (not required)
>   - paymentStatus (not required)
>   
>   
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": {
>         "trackingId": "[]",
>         "totalPrice": "[]" 
>     }
>   }
>   ```
<hr>


> ### ```api\v201\sendVCode.add.php``` ``POST``
>
> #### Required fields:
>   - **phone**  _should start with 09..._
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": {
>       "phone": "[userPhone]"
>     }
>   }
>   ```
<hr>


> ### ```api\v201\login.modify.php``` ``POST``
>
> #### Required fields:
>   - **phone**  _should start with 09..._
>   - **vCode**  
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": {
>         "isUserInfoSaved": "true/false",
>         "token": "[32-64 character]"
>     }
>   }
>   ```
<hr>

> ### ```api\v201\signup.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **name**  
>   - birthday (not required)
>   - job  (not required)
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>

> ### ```api\v201\changeUserInfo.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - name (not required)
>   - birthday (not required)
>   - job  (not required)
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>

> ### ```api\v201\getOrderByTrackingId.fetch.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **englishName**
>   - **trackingId** _number or list of numbers_
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": "[order info or List of orders info]"
>   }
>   ```
<hr>

> ### ```api\v201\getUserInfo.fetch.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": {
>            "name": "[name]",
>            "phone": "[phone]",
>            "birthday": "[birthday]",
>            "job": "[job]",
>            "allTotalBought": "[amount]",
>            "favoritePlaces": "[favorite_places]"    
>     }
>   }
>   ```
<hr>

> ### ```api\v201\getCustomerInfo.fetch.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **englishName**
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": {
>            "phone": "[phone]",
>            "totalBought": "[totalBought]",
>            "orderTimes": "[orderTimes]",
>            "score": "[score]",
>            "orderList": "[orderList]",
>            "rank": "[rank]",
>            "lastOrderDate": "[lastOrderDate]"   
>     }
>   }
>   ```
<hr>