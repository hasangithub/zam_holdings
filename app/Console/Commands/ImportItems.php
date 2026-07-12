<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemsImport;

class ImportItems extends Command
{

    protected $signature = 'items:import';


    protected $description = 'Import categories and items from Excel';



    public function handle()
    {


        $file = public_path('imports/items.xlsx');


        if(!file_exists($file))
        {

            $this->error('Excel file not found');

            return;

        }



        Excel::import(
            new ItemsImport,
            $file
        );



        $this->info('Items imported successfully');


    }

}