<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            ['id' => '1','name_en' => 'KABUL','name_dr' => 'کابل','name_pa' => 'کابل','zone' => '01','zname_dr' => 'کابل','zname_pa' => 'کابل','zname_en' => 'KABUL','latitude' => '34.5184042259092','longitude' => '69.201296853017','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name_en' => 'KAPISA','name_dr' => 'کاپیسا','name_pa' => 'کاپیسا','zone' => '02','zname_dr' => 'پروان','zname_pa' => 'پروان','zname_en' => 'PARWAN','latitude' => '35.0451066148951','longitude' => '69.3314056615201','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name_en' => 'PARWAN','name_dr' => 'پروان','name_pa' => 'پروان','zone' => '02','zname_dr' => 'پروان','zname_pa' => 'پروان','zname_en' => 'PARWAN','latitude' => '35.004041552873','longitude' => '9.1689032925095','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '4','name_en' => 'WARDAK','name_dr' => 'وردک','name_pa' => 'وردک','zone' => '01','zname_dr' => 'کابل','zname_pa' => 'کابل','zname_en' => 'KABUL','latitude' => '34.3963153321221','longitude' => '68.8655982034757','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '5','name_en' => 'LOGAR','name_dr' => 'لوگر','name_pa' => 'لوگر','zone' => '01','zname_dr' => 'کابل','zname_pa' => 'کابل','zname_en' => 'KABUL','latitude' => '33.9921477742856','longitude' => '69.0276052508786','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '6','name_en' => 'NANGARHAR','name_dr' => 'ننگرهار','name_pa' => 'ننگرهار','zone' => '09','zname_dr' => 'ننگرهار','zname_pa' => 'ننگرهار','zname_en' => 'NANGARHAR','latitude' => '34.4220126530481','longitude' => '70.4500198890865','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '7','name_en' => 'LAGHMAN','name_dr' => 'لغمان','name_pa' => 'لغمان','zone' => '09','zname_dr' => 'ننگرهار','zname_pa' => 'ننگرهار','zname_en' => 'NANGARHAR','latitude' => '34.6631994421109','longitude' => '70.2090416827914','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '8','name_en' => 'PANJSHER','name_dr' => 'پنجشیر','name_pa' => 'پنجشیر','zone' => '02','zname_dr' => 'پروان','zname_pa' => 'پروان','zname_en' => 'PARWAN','latitude' => '35.2709403744565','longitude' => '69.4785537165976','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '9','name_en' => 'BAGHLAN','name_dr' => 'بغلان','name_pa' => 'بغلان','zone' => '05','zname_dr' => 'تخار','zname_pa' => 'تخار','zname_en' => 'TAKHAR','latitude' => '35.944773925718900','longitude' => '68.705646243683800','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '10','name_en' => 'BAMYAN','name_dr' => 'بامیان','name_pa' => 'بامیان','zone' => '03','zname_dr' => 'بامیان','zname_pa' => 'بامیان','zname_en' => 'BAMYAN','latitude' => '34.8183782561839','longitude' => '67.8250519845061','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '11','name_en' => 'GHAZNI','name_dr' => 'غزنی','name_pa' => 'غزنی','zone' => '07','zname_dr' => 'غزنی','zname_pa' => 'غزنی','zname_en' => 'GHAZNI','latitude' => '33.5506601269994','longitude' => '68.4211631131052','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '12','name_en' => 'PAKTIKA','name_dr' => 'پکتیکا','name_pa' => 'پکتیکا','zone' => '07','zname_dr' => 'غزنی','zname_pa' => 'غزنی','zname_en' => 'GHAZNI','latitude' => '33.1584621556685','longitude' => '68.7931307334239','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '13','name_en' => 'PAKTYA','name_dr' => 'پکتیا','name_pa' => 'پکتیا','zone' => '06','zname_dr' => 'پکتیا','zname_pa' => 'پکتیا','zname_en' => 'PAKTYA','latitude' => '33.5944336513976','longitude' => '69.2315456946516','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '14','name_en' => 'KHOST','name_dr' => 'خوست','name_pa' => 'خوست','zone' => '06','zname_dr' => 'پکتیا','zname_pa' => 'پکتیا','zname_en' => 'PAKTYA','latitude' => '33.3397972358171','longitude' => '69.9248894825411','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '15','name_en' => 'KUNARHA','name_dr' => 'کنرها','name_pa' => 'کنرها','zone' => '09','zname_dr' => 'ننگرهار','zname_pa' => 'ننگرهار','zname_en' => 'NANGARHAR','latitude' => '34.8667314155401','longitude' => '71.1498493834973','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '16','name_en' => 'NOORISTAN','name_dr' => 'نورستان','name_pa' => 'نورستان','zone' => '09','zname_dr' => 'ننگرهار','zname_pa' => 'ننگرهار','zname_en' => 'NANGARHAR','latitude' => '35.6722436064465','longitude' => '71.3411278739911','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '17','name_en' => 'BADAKHSHAN','name_dr' => 'بدخشان','name_pa' => 'بدخشان','zone' => '05','zname_dr' => 'تخار','zname_pa' => 'تخار','zname_en' => 'TAKHAR','latitude' => '37.113583947131,','longitude' => '0.5812135264683 ','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '18','name_en' => 'TAKHAR','name_dr' => 'تخار','name_pa' => 'تخار','zone' => '05','zname_dr' => 'تخار','zname_pa' => 'تخار','zname_en' => 'TAKHAR','latitude' => '36.7359007089061','longitude' => '69.5409441054468','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '19','name_en' => 'KUNDUZ','name_dr' => 'کندوز','name_pa' => 'کندوز','zone' => '05','zname_dr' => 'تخار','zname_pa' => 'تخار','zname_en' => 'TAKHAR','latitude' => '36.7280001724505','longitude' => '68.862779603877','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '20','name_en' => 'SAMANGAN','name_dr' => 'سمنگان','name_pa' => 'سمنگان','zone' => '04','zname_dr' => 'بلخ','zname_pa' => 'بلخ','zname_en' => 'BALKH','latitude' => '36.2692660325555','longitude' => '68.0229872306625','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '21','name_en' => 'BALKH','name_dr' => 'بلخ','name_pa' => 'بلخ','zone' => '04','zname_dr' => 'بلخ','zname_pa' => 'بلخ','zname_en' => 'BALKH','latitude' => '36.7100839259961','longitude' => '67.1147882843912','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '22','name_en' => 'SAR-E-PUL','name_dr' => 'سرپل','name_pa' => 'سرپل','zone' => '04','zname_dr' => 'بلخ','zname_pa' => 'بلخ','zname_en' => 'BALKH','latitude' => '36.2190779443106','longitude' => '65.9340652736018','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '23','name_en' => 'GHOR','name_dr' => 'غور','name_pa' => 'غور','zone' => '03','zname_dr' => 'بامیان','zname_pa' => 'بامیان','zname_en' => 'BAMYAN','latitude' => '34.521539103989','longitude' => '5.2565447236648 ','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '24','name_en' => 'DAYKUNDI','name_dr' => 'دایکندی','name_pa' => 'دایکندی','zone' => '03','zname_dr' => 'بامیان','zname_pa' => 'بامیان','zname_en' => 'BAMYAN','latitude' => '33.7237552160148','longitude' => '66.1473663870644','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '25','name_en' => 'UROZGAN','name_dr' => 'اورزگان','name_pa' => 'اورزگان','zone' => '08','zname_dr' => 'کندهار','zname_pa' => 'کندهار','zname_en' => 'KANDAHAR','latitude' => '32.6209650244781','longitude' => '65.8759136127114','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '26','name_en' => 'ZABUL','name_dr' => 'زابل','name_pa' => 'زابل','zone' => '07','zname_dr' => 'غزنی','zname_pa' => 'غزنی','zname_en' => 'GHAZNI','latitude' => '32.1128796666514','longitude' => '66.9110591172556','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '27','name_en' => 'KANDAHAR','name_dr' => 'کندهار','name_pa' => 'کندهار','zone' => '08','zname_dr' => 'کندهار','zname_pa' => 'کندهار','zname_en' => 'KANDAHAR','latitude' => '31.6236899648026','longitude' => '65.7079154863354','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '28','name_en' => 'JAWZJAN','name_dr' => 'جوزجان','name_pa' => 'جوزجان','zone' => '04','zname_dr' => 'بلخ','zname_pa' => 'بلخ','zname_en' => 'BALKH','latitude' => '36.6651959657002','longitude' => '65.757454079303 ','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '29','name_en' => 'FARYAB','name_dr' => 'فاریاب','name_pa' => 'فاریاب','zone' => '04','zname_dr' => 'بلخ','zname_pa' => 'بلخ','zname_en' => 'BALKH','latitude' => '35.9180544610172','longitude' => '64.7778984945136','created_at' => '2020-06-20 06:44:58','updated_at' => '2020-06-20 06:44:58'],
            ['id' => '30','name_en' => 'HELMAND','name_dr' => 'هــلـمــند','name_pa' => 'هــلـمــند','zone' => '08','zname_dr' => 'کندهار','zname_pa' => 'کندهار','zname_en' => 'KANDAHAR','latitude' => '31.5850103172586','longitude' => '64.3696670624767','created_at' => '2020-06-20 06:44:59','updated_at' => '2020-06-20 06:44:59'],
            ['id' => '31','name_en' => 'BADGHIS','name_dr' => 'بادغیس','name_pa' => 'بادغیس','zone' => '10','zname_dr' => 'هرات','zname_pa' => 'هرات','zname_en' => 'HERAT','latitude' => '34.9886870571679','longitude' => '63.1254793011613','created_at' => '2020-06-20 06:44:59','updated_at' => '2020-06-20 06:44:59'],
            ['id' => '32','name_en' => 'HERAT','name_dr' => 'هرات','name_pa' => 'هرات','zone' => '10','zname_dr' => 'هرات','zname_pa' => 'هرات','zname_en' => 'HERAT','latitude' => '34.287429','longitude' => '62.206686','created_at' => '2020-06-20 06:44:59','updated_at' => '2020-06-20 06:44:59'],
            ['id' => '33','name_en' => 'FARAH','name_dr' => 'فراه','name_pa' => 'فراه','zone' => '10','zname_dr' => 'هرات','zname_pa' => 'هرات','zname_en' => 'HERAT','latitude' => '32.3714744883451','longitude' => '62.1161684405442','created_at' => '2020-06-20 06:44:59','updated_at' => '2020-06-20 06:44:59'],
            ['id' => '34','name_en' => 'NIMROZ','name_dr' => 'نیمروز','name_pa' => 'نیمروز','zone' => '10','zname_dr' => 'هرات','zname_pa' => 'هرات','zname_en' => 'HERAT','latitude' => '30.9584522554952','longitude' => '61.8589825138862','created_at' => '2020-06-20 06:44:59','updated_at' => '2020-06-20 06:44:59']
        ];

        foreach($provinces as $province){

            Province::create($province);
        }
    }
}
