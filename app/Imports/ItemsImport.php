<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ItemsImport implements ToCollection
{

    public function collection(Collection $rows)
    {

        $currentCategory = null;


        foreach ($rows as $row)
        {


            $colA = trim($row[0] ?? '');
            $colB = trim($row[1] ?? '');



            // skip empty rows

            if (!$colA && !$colB) {
                continue;
            }



            /*
            |--------------------------------------------------------------------------
            | CATEGORY ROW
            |--------------------------------------------------------------------------
            */

            if (is_numeric($colA))
            {


                $currentCategory = Category::firstOrCreate(

                    [
                        'name' => $colB
                    ],
                );


                continue;

            }



            /*
            |--------------------------------------------------------------------------
            | ITEM ROW
            |--------------------------------------------------------------------------
            */


            if(str_starts_with($colA,'ZAM-') && $currentCategory)
            {


                Item::firstOrCreate(

                    [
                        'item_code'=>$colA
                    ],

                    [

                        'category_id'=>$currentCategory->id,

                        'name'=>$colB,

                        ///'unit'=>'KG',

                        'item_type'=>1

                    ]

                );


            }


        }

    }

}