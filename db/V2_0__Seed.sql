USE restaurant;

INSERT INTO `customer` (`id`, `first_name`, `last_name`)
VALUES (NULL, 'Lucas', 'Maxwell'),
       (NULL, 'Samantha', 'Carpenter'),
       (NULL, 'Sonya', 'Sandoval'),
       (NULL, 'Gordon', 'Franklin'),
       (NULL, 'Russell', 'Benson');

INSERT INTO `restaurant` (`id`, `restaurant`, `cuisine`)
VALUES (NULL, 'dominos', 'pizza'),
       (NULL, 'mcdonalds', 'burgers'),
       (NULL, 'kfc', 'burgers');

INSERT INTO `menu` (`id`, `restaurant_id`, `item`, `price`, `available`)
VALUES (NULL, 1, 'hawaiian pizza', 9.99, true),
       (NULL, 1, 'bbq pizza', 10.99, false),
       (NULL, 1, 'margherita pizza', 5.99, false),
       (NULL, 1, 'pepperoni pizza', 7.99, true),
       (NULL, 2, 'cheese burger', 2.99, true),
       (NULL, 2, 'beef burger', 1.99, true),
       (NULL, 2, 'cheese burger', 1.99, false),
       (NULL, 2, 'chicken nuggets', 3.99, true),
       (NULL, 2, 'wrap', 3.49, true),
       (NULL, 3, 'chicken pieces', 2.79, true),
       (NULL, 3, 'chicken burger', 3.42, true),
       (NULL, 3, 'wrap', 2.68, false);

INSERT INTO `order` (`id`, `customer_id`)
VALUES (NULL, 1),
       (NULL, 2),
       (NULL, 5),
       (NULL, 4);
--        (NULL, 3);

INSERT INTO `ordered` (`id`, `order_id`, `item_id`, `price_charged`, `discount`)
VALUES (NULL, 1, 1, 9.99, 0.0),
       (NULL, 2, 2, 10.99, 0.0),
       (NULL, 2, 3, 5.99, 0.0),
       (NULL, 3, 9, 2.62, 0.25),
       (NULL, 3, 9, 2.62, 0.25),
       (NULL, 3, 8, 3.00, 0.25),
       (NULL, 4, 1, 9.99, 0.0);
       --(NULL, 5, 6, 1.99, 0.0);

INSERT INTO `transaction`  (`id`, `customer_id`, `ordered_id`, `tip`, `paid`)
VALUES (NULL, 1, 1, 0, 9.99),
       (NULL, 3, 2, 0, 10.99),
       (NULL, 4, 3, 0, 5.99),
       (NULL, 5, 4, 0, 2.62),
       (NULL, 5, 5, 0, 2.62);
--        (NULL, 3, 8, 0, 1.99),
--        (NULL, 3, 8, 1, 0.01)