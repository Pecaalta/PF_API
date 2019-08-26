<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\model\Sector as mSector;

class sector extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    const SECTOR = '';
    public function run()
    {
        mSector::create(array(
            'name' => 'Agricola',
            'img' => 'assets/sector/1.svg',
            'active' => true
        ));
        mSector::create(array(
            'name' => 'Industrial',
            'img' => 'assets/sector/2.svg',
            'active' => true
        ));
        mSector::create(array(
            'name' => 'Agroindustrial',
            'img' => 'assets/sector/1.svg',
            'active' => true
        ));
        mSector::create(array(
            'name' => 'Pecuario',
            'img' => 'assets/sector/2.svg',
            'active' => true
        ));
        mSector::create(array(
            'name' => 'Comercial',
            'img' => 'assets/sector/1.svg',
            'active' => true
        ));
        mSector::create(array(
            'name' => 'Servicios',
            'img' => 'assets/sector/2.svg',
            'active' => true
        ));
        mSector::create(array(
            'name' => 'Turismo',
            'img' => 'assets/sector/1.svg',
            'active' => true
        ));
        mSector::create(array(
            'name' => 'Maufactura',
            'img' => 'assets/sector/2.svg',
            'active' => true,
        ));
    }
}
