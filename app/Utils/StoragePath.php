<?php

namespace App\Utils;

class StoragePath
{

    public static function userProfilePath($user_id) {
        return "uploads/profile/$user_id";
    }

    public static function propertyPath($property_id) {
        return "uploads/properties/$property_id";
    }

    public static function transactionPath($transaction_id) {
        return "uploads/transactions/$transaction_id";
    }
}
