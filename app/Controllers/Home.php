<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        echo "<h2>Home page works!</h2>";
        echo "<p>Use /dbtest or /home/dbtest to check database connection.</p>";
    }

    public function dbtest()
    {
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');

            echo "<h2>Database connected successfully!</h2>";
        } catch (\Throwable $e) {
            echo "<h2>Database connection failed!</h2>";
            echo "<pre>";
            echo esc($e->getMessage());
            echo "</pre>";
        }
    }
}
