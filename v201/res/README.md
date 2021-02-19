##Restaurants:

> ### ```api\v201\res\createCategory.add.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **catPersianName** 
>   - **catEnglishName**
>   - **logo**
>   - **type**
>   - **resEnglishName**
>   - **averageColor**
>   - rank (not required)
>   
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>


> ### ```api\v201\res\createNewFood.add.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **name**
>   - **group** (english name of category) 
>   - **details** (separate them by `+`)
>   - price (not required)
>   - status (not required) 
>   - deliveryTime (not required)
>   - thumbnail (not required)
>   
>   
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>


> ### ```api\v201\res\loginRes.fetch.php``` ``POST``
>
> #### Required fields:
>   - **username**
>   - **password**
>   
>   
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data":{
>       "token": "[token]",
>       "position": "[position]",
>       "username": "[username]",
>       "resPersianName": "[persianName]",
>       "resEnglishName": "[englishName]"
>     }
>   }
>   ```
<hr>


> ### ```api\v201\res\getOrdersList.fetch.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **startDate**
>   - **endDate**
>   
>   
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>



> ### ```api\v201\res\submitOrderSavedCounterApp.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **trackingId**
>   
>   
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>


> ### ```api\v201\res\changeOrderStatus.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **trackingId**
>   - **newOrderStatus** 
>   - deleteReason
>   - deliveryId
>   
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>


> ### ```api\v201\res\getFoodsList.fetch.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": []
>   }
>   ```
<hr>


> ### ```api\v201\res\changeFoodInfo.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **foodId**
>   - persianName 
>   - group 
>   - details [_separate them by "+"_]
>   - price
>   - status ```one of: ["in stock", "out of stock", "deleted"]```
>   - discount [_in percentage_]
>   - deliveryTime [_in minute_]
>   - counterAppFoodId
>   - foodThumbnail ```image file```
>
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>
