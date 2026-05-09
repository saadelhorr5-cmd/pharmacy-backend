<?php

namespace App\Http\Controllers;

class BackupController extends Controller
{
    public function backup()
    {
        $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';

        $filePath = storage_path($fileName);

        $mysqlPath = "C:/xampp/mysql/bin/mysqldump.exe";

        $database = "pharmacy";

        $command = "$mysqlPath --user=root $database > \"$filePath\"";

        exec($command);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}