<?php
/*
*
*** I don't take any responsibility if the dates are incorrect 
* 
*** Free to use and do whatever you want with it
*
*/

function swedishHolidays($format=false, $calStart=false, $calEnd=false){
    /*
    *** formats can be:
        false (returns array, default)
        ics
        json
    */

    //* Define all static dates to the calendar
 
    $calStatic = array(
        "0101" => "Nyårsdagen",
        "0105" => "Trettondagsafton",
        "0106" => "Trettondedag Jul",
        "0430" => "Valborgsmässoafton",
        "0501" => "Första Maj",
        "0606" => "Sveriges nationaldag",
        '1224' => "Julafton",
        "1225" => "Juldagen",
        "1226" => "Annandag Jul",
        "1231" => "Nyårsafton",
    );


    //* Loop trough all defined years and get dynamic dates

    $cal = array();
    if(!$calStart || !is_numeric($calStart)){
        $calStart = date("Y") - 2;
    }

    if(!$calEnd || !is_numeric($calEnd)){
        $calEnd = date("Y") + 8;
    }

    if($calStart > $calEnd){ return False; }

    for($i=$calStart; $i<=$calEnd; $i++){
        
        //* Create a new year in array
        $cal[$i] = array();


        //* Add static dates to calendar
        foreach($calStatic as $key => $value){

            if($value == "Första Maj" && $i < 1939){
                continue;
            }

            if($value == "Sveriges nationaldag" && $i < 2005){
                continue;
            }

            $cal[$i][$i.$key] = $value;
        }

        /*
        ************ Calculate Month and day for Easter-related dates ************
        */

        $a = $i%19;
        $b = floor($i/100);
        $c = $i%100;

        $h = ((19*$a) + $b - floor($b/4) - floor(($b - floor(($b + 8)/25) + 1)/3) + 15)%30;
        $l = (32 + (2*($b%4)) + (2*(floor($c/4))) - $h - ($c%4))%7;
        $m = floor(($a + (11*$h) + (22*$l))/451);

        $easterMonth= sprintf('%02d', (floor(($h + $l - (7*$m) + 114)/31)));
        $easterDay = sprintf('%02d', ((($h + $l - (7*$m) + 114)%31)+1));


        //* Add easter-related dates to the array
        $cal[$i][date("Ymd", strtotime($i.$easterMonth.$easterDay. "-3 days"))] = "Skärtorsdagen";
        $cal[$i][date("Ymd", strtotime($i.$easterMonth.$easterDay. "-2 days"))] = "Långfredagen";
        $cal[$i][date("Ymd", strtotime($i.$easterMonth.$easterDay. "-1 days"))] = "Påskafton";
        $cal[$i][$i.$easterMonth.$easterDay] = "Påskdagen";
        $cal[$i][date("Ymd", strtotime($i.$easterMonth.$easterDay. "+1 days"))] = "Annandag påsk";
        
        $cal[$i][date("Ymd", strtotime($i.$easterMonth.$easterDay. "+39 days"))] = "Kristi himmelsfärdsdag";
        $cal[$i][date("Ymd", strtotime($i.$easterMonth.$easterDay. "+48 days"))] = "Pingstafton";
        $cal[$i][date("Ymd", strtotime($i.$easterMonth.$easterDay. "+49 days"))] = "Pingstdagen";

        /*
        **************************************************************************
        */


        /*
        *********** Calculate Month and day for Midsummer-related dates **********
        */

        //* Loop trough possible dates for midsummmer

        for($j=19; $j<=25; $j++){

            if(date("N", strtotime($i."06".$j)) == 5){
                $midsummerDay = $j;
                break;
            }
        }

        //* Add midsummer-related dates to the array
        $cal[$i][$i."06".$midsummerDay] = "Midsommarafton";
        $cal[$i][date("Ymd", strtotime($i."06".$midsummerDay. "+1 days"))] = "Midsommardagen";

        /*
        **************************************************************************
        */

        /*
        ********** Calculate Month and day for All Saints-related dates **********
        */

        if($i >= 1953){

            for($k=30; $k<=36; $k++){
                
                $asMonth = 10;
                $asDay = $k;
                
                if($k > 31){
                    $asMonth = 11;
                    $asDay = sprintf('%02d', ($k-31));
                }
    
                if(date("N", strtotime($i.$asMonth.$asDay)) == 5){
                    break;
                }
            }

        }elseif($i >= 1772){
            
            $asMonth = 11;

            for($l=1;$l<=7;$l++){
                if(date("N", strtotime($i.$asMonth."0".$l."-1 days")) == 6){
                    $asDay = date("d", strtotime($i.$asMonth."0".$l."-1 days"));
                    $asMonth = date("m", strtotime($i.$asMonth."0".$l."-1 days"));
                    break;
                }
            }

        }else{
            $asMonth = 10;
            $asDay = 31;
        }
        
        $cal[$i][$i.$asMonth.$asDay] = "Allhelgonaafton";
        $cal[$i][date("Ymd", strtotime($i.$asMonth.$asDay. "+1 days"))] = "Alla helgons dag";

        /*
        **************************************************************************
        */


        //* Sort calendar
        ksort($cal[$i]);
    }

    //* Check wich format is requested

    if(strtolower($format) == "ics"){

        $return = "BEGIN:VCALENDAR\nVERSION:2.0 \nCALSCALE:GREGORIAN\nNAME;VALUE=TEXT:Svenska Helgdagar\nX-WR-CALNAME;VALUE=TEXT:Svenska Helgdagar\n";
        
        foreach($cal as $year){
            foreach($year as $date => $event){
                
                $return .= "BEGIN:VEVENT\nX-FUNAMBOL-ALLDAY:1\nX-MICROSOFT-CDO-ALLDAYEVENT:TRUE\nSUMMARY;VALUE=TEXT:".$event."\nDTSTART;VALUE=DATE:".$date."\nDTEND;VALUE=DATE:".date("Ymd", strtotime($date. "+1 days"))."\nEND:VEVENT\n";
            }
        }

        $return .= "END:VCALENDAR";
        
        return $return;

    }
    
    if(strtolower($format) == "json"){
        return json_encode($cal, JSON_UNESCAPED_UNICODE);
    }
    
    return $cal;
}