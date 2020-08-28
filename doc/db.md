GBM.DB
DB Version : 3.1 - 28.08.2020
Source file Cryptolex.Cloud.Documentation


In order to allow for a larger authorization field, the table is modified as follows

Table **GBM_SYS_Right** :

A new column is added **ID_Type** and a new Column ID_Block is renamed to **ID_Related**

Example for Block Right : 

| ID_User | ID_Block | S | I | U | D |
|---------|----------|---|---|---|---|
| 5       | 10       | 1 | 1 | 1 | 0 |

Will become

| ID_User | ID_Type | ID_Related | S | I | U | D |
|---------|---------|------------|---|---|---|---|
| 5       | 3005    | 10         | 1 | 1 | 1 | 0 |

Impact on class right.class.php


# GBM_SYS_Var
GBM_SYS_Var store all the values that can be used into a standard database. Line from 1-99'999 are reserved for GBM you can start adding Datas from 100'000 

From    |To     |Values type|Grp|Comment
--------|-------|---|---|---
0       |99     |FALSE, TRUE, No, Yes, Male, Female, Other||
10      |29     |System Status||
30      |39     |User Role||
40      |74     |Contact Type||
75      |79     |Relation Type||
100     |399    |Country name  ||  ISO 3166-2  
400     |699    |Currency code  || ISO 4217    
700     |899    |GMT Cities list||
900     ¦999    |Language   |Language   |639-1
1000    |1019   |Delivery terms Incoterms
1020    |1039   |Payment terms
-||||
1100    |1119   |Blockchain Network||
-||||
1200    |1299   |Metrics||
1300    |1399   |Units||
1400    |1499   |preg||
1500    |1599   |Document||
2000    |2499   |EntityType
2500    |2999   |Action
-||||
100000  |-      |User Datas||

Populating still on process 