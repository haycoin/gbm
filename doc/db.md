# GBM_SYS_Var
GBM_SYS_Var store all the values that can be used into a standard database. Line from 1-99'999 are reserved for GBM you can start adding Datas from 100'000 

From    |To     |Values type|Grp|Comment
--------|-------|---|---|---
0       |99     |FALSE, TRUE, No, Yes, Male, Female, Other||
10      |29     |System Status||
30      |39     |User Role||
40      |79     |Contact Type||
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
-||||
100000  |-      |User Datas||

Populating still on process 