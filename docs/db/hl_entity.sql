SELECT * FROM b_hlblock_entity WHERE ID >= 6;
+----+-----------------+----------------------+
| ID | NAME            | TABLE_NAME           |
+----+-----------------+----------------------+
|  6 | ProductBrand    | pet_product_brand    |
|  7 | ProductColor    | pet_product_color    |
|  8 | ProductMaterial | pet_product_material |
|  9 | ProductSize     | pet_product_size     |
| 10 | ProductCountry  | pet_product_country  |
+----+-----------------+----------------------+
5 rows in set (0.00 sec)

INSERT INTO `bitrix-pet-shop`.b_hlblock_entity (ID, NAME, TABLE_NAME) VALUES (6, 'ProductBrand', 'pet_product_brand');
INSERT INTO `bitrix-pet-shop`.b_hlblock_entity (ID, NAME, TABLE_NAME) VALUES (7, 'ProductColor', 'pet_product_color');
INSERT INTO `bitrix-pet-shop`.b_hlblock_entity (ID, NAME, TABLE_NAME) VALUES (8, 'ProductMaterial', 'pet_product_material');
INSERT INTO `bitrix-pet-shop`.b_hlblock_entity (ID, NAME, TABLE_NAME) VALUES (9, 'ProductSize', 'pet_product_size');
INSERT INTO `bitrix-pet-shop`.b_hlblock_entity (ID, NAME, TABLE_NAME) VALUES (10, 'ProductCountry', 'pet_product_country');
