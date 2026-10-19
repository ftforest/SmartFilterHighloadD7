
     -- ProductBrand
ALTER TABLE `pet_product_brand`
    ADD INDEX `idx_product` (`UF_PRODUCT_ID`),
    ADD INDEX `idx_value` (`UF_VALUE_ID`),
    ADD INDEX `idx_product_value` (`UF_PRODUCT_ID`, `UF_VALUE_ID`);

-- ProductColor
ALTER TABLE `pet_product_color`
    ADD INDEX `idx_product` (`UF_PRODUCT_ID`),
    ADD INDEX `idx_value` (`UF_VALUE_ID`),
    ADD INDEX `idx_product_value` (`UF_PRODUCT_ID`, `UF_VALUE_ID`);

-- ProductMaterial
ALTER TABLE `pet_product_material`
    ADD INDEX `idx_product` (`UF_PRODUCT_ID`),
    ADD INDEX `idx_value` (`UF_VALUE_ID`),
    ADD INDEX `idx_product_value` (`UF_PRODUCT_ID`, `UF_VALUE_ID`);

-- ProductSize
ALTER TABLE `pet_product_size`
    ADD INDEX `idx_product` (`UF_PRODUCT_ID`),
    ADD INDEX `idx_value` (`UF_VALUE_ID`),
    ADD INDEX `idx_product_value` (`UF_PRODUCT_ID`, `UF_VALUE_ID`);

-- ProductCountry
ALTER TABLE `pet_product_country`
    ADD INDEX `idx_product` (`UF_PRODUCT_ID`),
    ADD INDEX `idx_value` (`UF_VALUE_ID`),
    ADD INDEX `idx_product_value` (`UF_PRODUCT_ID`, `UF_VALUE_ID`);

SHOW INDEX FROM pet_product_brand;
SHOW INDEX FROM pet_product_color;
SHOW INDEX FROM pet_product_material;
SHOW INDEX FROM pet_product_size;
SHOW INDEX FROM pet_product_country;

 -- data index highload in bd

INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_brand', 0, 'PRIMARY', 1, 'ID', 'A', 48623, null, null, '', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_brand', 1, 'idx_product', 1, 'UF_PRODUCT_ID', 'A', 51474, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_brand', 1, 'idx_value', 1, 'UF_VALUE_ID', 'A', 7, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_brand', 1, 'idx_product_value', 1, 'UF_PRODUCT_ID', 'A', 50100, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_brand', 1, 'idx_product_value', 2, 'UF_VALUE_ID', 'A', 50100, null, null, 'YES', 'BTREE', '', '', 'YES', null);

INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_color', 0, 'PRIMARY', 1, 'ID', 'A', 75171, null, null, '', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_color', 1, 'idx_product', 1, 'UF_PRODUCT_ID', 'A', 50609, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_color', 1, 'idx_value', 1, 'UF_VALUE_ID', 'A', 6, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_color', 1, 'idx_product_value', 1, 'UF_PRODUCT_ID', 'A', 48537, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_color', 1, 'idx_product_value', 2, 'UF_VALUE_ID', 'A', 76299, null, null, 'YES', 'BTREE', '', '', 'YES', null);

INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_material', 0, 'PRIMARY', 1, 'ID', 'A', 75364, null, null, '', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_material', 1, 'idx_product', 1, 'UF_PRODUCT_ID', 'A', 50942, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_material', 1, 'idx_value', 1, 'UF_VALUE_ID', 'A', 4, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_material', 1, 'idx_product_value', 1, 'UF_PRODUCT_ID', 'A', 50190, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_material', 1, 'idx_product_value', 2, 'UF_VALUE_ID', 'A', 75125, null, null, 'YES', 'BTREE', '', '', 'YES', null);

INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_size', 0, 'PRIMARY', 1, 'ID', 'A', 100411, null, null, '', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_size', 1, 'idx_product', 1, 'UF_PRODUCT_ID', 'A', 50427, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_size', 1, 'idx_value', 1, 'UF_VALUE_ID', 'A', 5, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_size', 1, 'idx_product_value', 1, 'UF_PRODUCT_ID', 'A', 52006, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_size', 1, 'idx_product_value', 2, 'UF_VALUE_ID', 'A', 103539, null, null, 'YES', 'BTREE', '', '', 'YES', null);

INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_country', 0, 'PRIMARY', 1, 'ID', 'A', 50115, null, null, '', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_country', 1, 'idx_product', 1, 'UF_PRODUCT_ID', 'A', 50186, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_country', 1, 'idx_value', 1, 'UF_VALUE_ID', 'A', 5, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_country', 1, 'idx_product_value', 1, 'UF_PRODUCT_ID', 'A', 50100, null, null, 'YES', 'BTREE', '', '', 'YES', null);
INSERT INTO MY_TABLE(`Table`, Non_unique, Key_name, Seq_in_index, Column_name, Collation, Cardinality, Sub_part, Packed, `Null`, Index_type, Comment, Index_comment, Visible, Expression) VALUES ('pet_product_country', 1, 'idx_product_value', 2, 'UF_VALUE_ID', 'A', 50100, null, null, 'YES', 'BTREE', '', '', 'YES', null);

