<?php

namespace Test\behat\bootstrap;


use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Imbo\BehatApiExtension\Context\ApiContext;

class FeatureContext extends ApiContext
{
    /** @AfterFeature */
    public static function teardownFeature(AfterFeatureScope $scope)
    {
        //Reset Database
        $pdo = new \pdo("mysql:host=localhost:3306; dbname=restaurant;", getenv('DB_USER'), getenv('DB_PASSWORD'));

        $query = $pdo->query("TRUNCATE TABLE `customer`;
                                        TRUNCATE TABLE `restaurant`;
                                        TRUNCATE TABLE `menu`;
                                        TRUNCATE TABLE `order`;
                                        TRUNCATE TABLE `ordered`;
                                        TRUNCATE TABLE `transaction`;
                                        ALTER TABLE `customer` AUTO_INCREMENT = 1;
                                        ALTER TABLE `restaurant` AUTO_INCREMENT = 1;
                                        ALTER TABLE `menu` AUTO_INCREMENT = 1;
                                        ALTER TABLE `order` AUTO_INCREMENT = 1;
                                        ALTER TABLE `ordered` AUTO_INCREMENT = 1;
                                        ALTER TABLE `transaction` AUTO_INCREMENT = 1;");
        $query->execute();
    }
}