##Restaurants:

> ### ```api/v201/res/createCategory.add.php``` ``POST``
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


> ### ```api/v201/res/createNewFood.add.php``` ``POST``
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


> ### ```api/v201/res/loginRes.fetch.php``` ``POST``
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


> ### ```api/v201/res/getOrdersList.fetch.php``` ``POST``
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



> ### ```api/v201/res/submitOrderSavedCounterApp.modify.php``` ``POST``
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


> ### ```api/v201/res/changeOrderStatus.modify.php``` ``POST``
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


> ### ```api/v201/res/getFoodsList.fetch.php``` ``POST``
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


> ### ```api/v201/res/getCategoriesList.fetch.php``` ``POST``
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


> ### ```api/v201/res/getResInfo.fetch.php``` ``POST``
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



> ### ```api/v201/res/changeFoodInfo.modify.php``` ``POST``
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

> ### ```api/v201/res/changeResInfo.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - persianName 
>   - englishName 
>   - status
>   - counterPhone
>   - phone [_array_]
>   - addressText
>   - addressLink 
>   - owner 
>   - employers [_object_] ```{"chef":"علی", ...}```
>   - socialLinks [_object_] ```{"instagram":"@cuki", ...}```
>   - openTime [_object_] ```{"0":[14,15,16,...],"1":[],"2":[],"3":[],"4":[],"5":[],"6":[]}```
>   - type [_array_] ```["restaurant", "coffeeshope", ...]```
>   - minOrderPrice 
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>

> ### ```api/v201/res/changeResPass.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **previousPass**
>   - **newPass** 
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>


> ### ```api/v201/res/getPages.fetch.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   #### Return Values ``JSON``:
>   ```json
>   {
>     "statusCode": "[code]",
>     "data": []
>   }
>   ```
<hr>



> ### ```dl/uploadResLogo.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **logo** [_file_]
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>

> ### ```dl/uploadResFavicon.modify.php``` ``POST``
>
> #### Required fields:
>   - **token**
>   - **favicon** [_file_]
>   #### Return Values ``JSON``:
>   ```json
>   {"statusCode": "[code]"}
>   ```
<hr>