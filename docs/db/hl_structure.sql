SHOW CREATE TABLE pet_product_brand;
SHOW CREATE TABLE pet_product_color;
SHOW CREATE TABLE pet_product_material;
SHOW CREATE TABLE pet_product_size;
SHOW CREATE TABLE pet_product_country;

CREATE TABLE `pet_product_brand` (
     `ID` int unsigned NOT NULL AUTO_INCREMENT,
     `UF_PRODUCT_ID` int DEFAULT NULL,
     `UF_VALUE_ID` int DEFAULT NULL,
     `UF_VALUE` text,
     PRIMARY KEY (`ID`),
     KEY `idx_product` (`UF_PRODUCT_ID`),
     KEY `idx_value` (`UF_VALUE_ID`),
     KEY `idx_product_value` (`UF_PRODUCT_ID`,`UF_VALUE_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=50111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `pet_product_color` (
     `ID` int unsigned NOT NULL AUTO_INCREMENT,
     `UF_PRODUCT_ID` int DEFAULT NULL,
     `UF_VALUE_ID` int DEFAULT NULL,
     `UF_VALUE` text,
     PRIMARY KEY (`ID`),
     KEY `idx_product` (`UF_PRODUCT_ID`),
     KEY `idx_value` (`UF_VALUE_ID`),
     KEY `idx_product_value` (`UF_PRODUCT_ID`,`UF_VALUE_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=75102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `pet_product_material` (
    `ID` int unsigned NOT NULL AUTO_INCREMENT,
    `UF_PRODUCT_ID` int DEFAULT NULL,
    `UF_VALUE_ID` int DEFAULT NULL,
    `UF_VALUE` text,
    PRIMARY KEY (`ID`),
    KEY `idx_product` (`UF_PRODUCT_ID`),
    KEY `idx_value` (`UF_VALUE_ID`),
    KEY `idx_product_value` (`UF_PRODUCT_ID`,`UF_VALUE_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=75150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `pet_product_size` (
    `ID` int unsigned NOT NULL AUTO_INCREMENT,
    `UF_VALUE` text,
    `UF_PRODUCT_ID` int DEFAULT NULL,
    `UF_VALUE_ID` int DEFAULT NULL,
    PRIMARY KEY (`ID`),
    KEY `idx_product` (`UF_PRODUCT_ID`),
    KEY `idx_value` (`UF_VALUE_ID`),
    KEY `idx_product_value` (`UF_PRODUCT_ID`,`UF_VALUE_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=100207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

CREATE TABLE `pet_product_country` (
   `ID` int unsigned NOT NULL AUTO_INCREMENT,
   `UF_PRODUCT_ID` int DEFAULT NULL,
   `UF_VALUE_ID` int DEFAULT NULL,
   `UF_VALUE` text,
   PRIMARY KEY (`ID`),
   KEY `idx_product` (`UF_PRODUCT_ID`),
   KEY `idx_value` (`UF_VALUE_ID`),
   KEY `idx_product_value` (`UF_PRODUCT_ID`,`UF_VALUE_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=50104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci