<?php

/* 
 * Proses mining function
 */

function reset_temporary($db_object){
    $sql1 = "TRUNCATE itemset1";
    $db_object->db_query($sql1);
    
    $sql2 = "TRUNCATE itemset2";
    $db_object->db_query($sql2);
    
    $sql3 = "TRUNCATE itemset3";
    $db_object->db_query($sql3);
    
    $sql4 = "TRUNCATE confidence";
    $db_object->db_query($sql4);
}

function reset_hitungan($db_object, $id_process){
    $condition = array("id_process"=>$id_process);
    $db_object->delete_record("itemset1", $condition);
    
    //$condition = array("id_process"=>$id_process);
    $db_object->delete_record("itemset2", $condition);
    
    //$condition = array("id_process"=>$id_process);
    $db_object->delete_record("itemset3", $condition);
    
    //$condition = array("id_process"=>$id_process);
    $db_object->delete_record("confidence", $condition);
}

function is_exist_variasi_itemset($array_item1, $array_item2, $item1, $item2) {
    //$return = true;
    
//    $bool1 = array_search(strtoupper($item2), array_map('strtoupper', $array_item1));
//    $bool2 = array_search(strtoupper($item1), array_map('strtoupper', $array_item2));
//    $bool3 = array_search(strtoupper($item2), array_map('strtoupper', $array_item2));
//    $bool4 = array_search(strtoupper($item1), array_map('strtoupper', $array_item1));
    $bool1 = array_keys(array_map('strtoupper', $array_item1), strtoupper($item1));
    $bool2 = array_keys(array_map('strtoupper', $array_item2), strtoupper($item2));
    $bool3 = array_keys(array_map('strtoupper', $array_item2), strtoupper($item1));
    $bool4 = array_keys(array_map('strtoupper', $array_item1), strtoupper($item2));
    
    foreach ($bool1 as $key => $value) {
        $aa = array_search($value, $bool2);
        if(is_numeric($aa)){
            return true;
        }
    }
    
    foreach ($bool3 as $key => $value) {
        $aa = array_search($value, $bool4);
        if(is_numeric($aa)){
            return true;
        }
    }
    
//    if (is_numeric($bool1) && is_numeric($bool2) || is_numeric($bool3) && is_numeric($bool4)){
//        if($bool1 === $bool2 || $bool3 === $bool4){
//            return true;
//        }
//    }
    
//    if (($bool3) && ($bool4)){
//        if($bool3 == $bool4){//jika ditemukan dengan idex yg sama
//            return true;
//        }
//    }
    
    return false;
}


function mining_process($db_object, $min_support, $min_confidence, $start_date, $end_date, $id_process){
    //remove reset truncate (change to log mode)
    //reset_temporary($db_object);
    //get  transaksi data to array variable
    $sql_trans = "SELECT * FROM transaksi 
            WHERE transaction_date BETWEEN '$start_date' AND '$end_date' ";
    $result_trans = $db_object->db_query($sql_trans);
    $dataTransaksi = $item_list = array();
    $jumlah_transaksi = $db_object->db_num_rows($result_trans);
    $min_support_relative = ($min_support/$jumlah_transaksi)*100; 
    $x=0;
    while($myrow = $db_object->db_fetch_array($result_trans)){
        $dataTransaksi[$x]['tanggal'] = $myrow['transaction_date'];
        $item_produk = $myrow['produk'].",";
        //mencegah ada jarak spasi
        $item_produk = str_replace(" ,", ",", $item_produk);
        $item_produk = str_replace("  ,", ",", $item_produk);
        $item_produk = str_replace("   ,", ",", $item_produk);
        $item_produk = str_replace("    ,", ",", $item_produk);
        $item_produk = str_replace(", ", ",", $item_produk);
        $item_produk = str_replace(",  ", ",", $item_produk);
        $item_produk = str_replace(",   ", ",", $item_produk);
        $item_produk = str_replace(",    ", ",", $item_produk);
        
        $dataTransaksi[$x]['produk'] = $item_produk;
        $produk = explode(",", $myrow['produk']);
        //all items
        foreach ($produk as $key => $value_produk) {
            //if(!in_array($value_produk, $item_list)){
            if(!in_array(strtoupper($value_produk), array_map('strtoupper', $item_list))){
                if(!empty($value_produk)){
                    $item_list[] = $value_produk;
                }
            }
        }
        $x++;
    }
    
    
    //build itemset 1
    echo "<br><strong>Itemset 1:</strong><br>";
    echo "<table class='table table-bordered table-striped  table-hover'>
            <tr>
                <th>No</th>
                <th>Item</th>
                <th>Jumlah</th>
                <th>Suppport</th>
                <th>Keterangan</th>
            </tr>";
    $itemset1 = $jumlahItemset1 = $supportItemset1 = $valueIn = array();
    $x=1;
    foreach ($item_list as $key => $item) {
        $jumlah = jumlah_itemset1($dataTransaksi, $item);
        $support = ($jumlah/$jumlah_transaksi) * 100;
        $lolos = ($support>=$min_support_relative)?"1":"0";
        $valueIn[] = "('$item','$jumlah','$support','$lolos','$id_process')";
        if($lolos){
            $itemset1[] = $item;//item yg lolos itemset1
            $jumlahItemset1[] = $jumlah;
            $supportItemset1[] = $support;
        }
        echo "<tr>";
        echo "<td>" . $x . "</td>";
        echo "<td>" . $item . "</td>";
        echo "<td>" . $jumlah . "</td>";
        echo "<td>" . price_format($support) . "</td>";
        echo "<td>" . (($lolos==1)?"Lolos":"Tidak Lolos") . "</td>";
        echo "</tr>";
        $x++;
    }
    echo "</table>";
    //insert into itemset1 one query with many value
    $value_insert = implode(",", $valueIn);
    $sql_insert_itemset1 = "INSERT INTO itemset1 (atribut, jumlah, support, lolos, id_process) "
            . " VALUES ".$value_insert;
    $db_object->db_query($sql_insert_itemset1);
    
    //display itemset yg lolos
    echo "<br><strong>Itemset 1 yang lolos:</strong><br>";
    echo "<table class='table table-bordered table-striped  table-hover'>
            <tr>
                <th>No</th>
                <th>Item</th>
                <th>Jumlah</th>
                <th>Suppport</th>
            </tr>";
    $x=1;
    foreach ($itemset1 as $key => $value) {
        echo "<tr>";
        echo "<td>" . $x . "</td>";
        echo "<td>" . $value . "</td>";
        echo "<td>" . $jumlahItemset1[$key] . "</td>";
        echo "<td>" . price_format($supportItemset1[$key]) . "</td>";
        echo "</tr>";
        $x++;
    }
    echo "</table>";
    
    
    //build itemset2
    echo "<br><strong>Itemset 2:</strong><br>";
    echo "<table class='table table-bordered table-striped  table-hover'>
            <tr>
                <th>No</th>
                <th>Item1</th>
                <th>Item2</th>
                <th>Jumlah</th>
                <th>Suppport</th>
                <th>Keterangan</th>
            </tr>";
    $NilaiAtribut1 = $NilaiAtribut2 = array();
    $itemset2_var1 = $itemset2_var2 = $jumlahItemset2 = $supportItemset2 = array();
    $valueIn_itemset2 = array();
    $no=1;
    $a = 0;
    while ($a <= count($itemset1)) {
        $b = 0;
        while ($b <= count($itemset1)) {
            $variance1 = $itemset1[$a];
            $variance2 = $itemset1[$b];
            if (!empty($variance1) && !empty($variance2)) {
                if ($variance1 != $variance2) {
                    if(!is_exist_variasi_itemset($NilaiAtribut1, $NilaiAtribut2, $variance1, $variance2)) {
                        //$jml_itemset2 = get_count_itemset2($db_object, $variance1, $variance2, $start_date, $end_date);
                        $jml_itemset2 = jumlah_itemset2($dataTransaksi, $variance1, $variance2);
                        $NilaiAtribut1[] = $variance1;
                        $NilaiAtribut2[] = $variance2;

                        $support2 = ($jml_itemset2/$jumlah_transaksi) * 100;
                        $lolos = ($support2 >= $min_support_relative)? 1:0;
                        
                        $valueIn_itemset2[] = "('$variance1','$variance2','$jml_itemset2','$support2','$lolos','$id_process')";
                        if($lolos){
                            $itemset2_var1[] = $variance1;
                            $itemset2_var2[] = $variance2;
                            $jumlahItemset2[] = $jml_itemset2;
                            $supportItemset2[] = $support2;
                        }
                        echo "<tr>";
                        echo "<td>" . $no . "</td>";
                        echo "<td>" . $variance1 . "</td>";
                        echo "<td>" . $variance2 . "</td>";
                        echo "<td>" . $jml_itemset2 . "</td>";
                        echo "<td>" . price_format($support2) . "</td>";
                        echo "<td>" . (($lolos==1)?"Lolos":"Tidak Lolos") . "</td>";
                        echo "</tr>";
                        $no++;
                    }
                }
            }
            $b++;
        }
        $a++;
    }
    echo "</table>";
    //insert into itemset2 one query with many value
    $value_insert_itemset2 = implode(",", $valueIn_itemset2);
    $sql_insert_itemset2 = "INSERT INTO itemset2 (atribut1, atribut2, jumlah, support, lolos, id_process) "
            . " VALUES ".$value_insert_itemset2;
    $db_object->db_query($sql_insert_itemset2);
    
    //display itemset yg lolos
    echo "<br><strong>Itemset 2 yang lolos:</strong><br>";
    echo "<table class='table table-bordered table-striped  table-hover'>
            <tr>
                <th>No</th>
                <th>Item 1</th>
                <th>Item 2</th>
                <th>Jumlah</th>
                <th>Suppport</th>
            </tr>";
    $no=1;
    foreach ($itemset2_var1 as $key => $value) {
        echo "<tr>";
        echo "<td>" . $no . "</td>";
        echo "<td>" . $value . "</td>";
        echo "<td>" . $itemset2_var2[$key] . "</td>";
        echo "<td>" . $jumlahItemset2[$key] . "</td>";
        echo "<td>" . price_format($supportItemset2[$key]) . "</td>";
        echo "</tr>";
        $no++;
    }
    echo "</table>";
    
    //build itemset3
    echo "<br><strong>Itemset 3:</strong><br>";
    echo "<table class='table table-bordered table-striped  table-hover'>
            <tr>
                <th>No</th>
                <th>Item1</th>
                <th>Item2</th>
                <th>Item3</th>
                <th>Jumlah</th>
                <th>Suppport</th>
                <th>Keterangan</th>
            </tr>";
    $a = 0;
    $tigaVariasiItem = $valueIn_itemset3 =  array();
    $itemset3_var1 = $itemset3_var2 = $itemset3_var3 = $jumlahItemset3 = $supportItemset3 = array();
    $no=1;
    while ($a <= count($itemset2_var1)) {
        $b = 0;
        while ($b <= count($itemset2_var1)) {
            if($a != $b){
                $itemset1a = $itemset2_var1[$a];
                $itemset1b = $itemset2_var1[$b];

                $itemset2a = $itemset2_var2[$a];
                $itemset2b = $itemset2_var2[$b];

                if (!empty($itemset1a) && !empty($itemset1b)&& !empty($itemset2a) && !empty($itemset2b)) {
                    
                    $temp_array = get_variasi_itemset3($tigaVariasiItem, 
                            $itemset1a, $itemset1b, $itemset2a, $itemset2b);
                    
                    if(count($temp_array)>0){
                        //variasi-variasi itemset isi ke array
                        $tigaVariasiItem = array_merge($tigaVariasiItem, $temp_array);
                        
                        foreach ($temp_array as $idx => $val_nilai) {
                            $itemset1 = $itemset2 = $itemset3 = "";
                            
                            $aaa=0;
                            foreach ($val_nilai as $idx1 => $v_nilai) {
                                if($aaa==0){
                                    $itemset1 = $v_nilai;
                                }
                                if($aaa==1){
                                    $itemset2 = $v_nilai;
                                }
                                if($aaa==2){
                                    $itemset3 = $v_nilai;
                                }
                                $aaa++;
                            }
                            
                            //jumlah item set3 dan menghitung supportnya
                            //$jml_itemset3 = get_count_itemset3($db_object, $itemset1, $itemset2, $itemset3, $start_date, $end_date);
                            $jml_itemset3 = jumlah_itemset3($dataTransaksi, $itemset1, $itemset2, $itemset3);
                            $support3 = ($jml_itemset3/$jumlah_transaksi) * 100;
                            $lolos = ($support3 >= $min_support_relative)? 1:0;
                            
                            $valueIn_itemset3[] = "('$itemset1','$itemset2','$itemset3','$jml_itemset3','$support3','$lolos','$id_process')";
                            
                            if($lolos){
                                $itemset3_var1[] = $itemset1;
                                $itemset3_var2[] = $itemset2;
                                $itemset3_var3[] = $itemset3;
                                $jumlahItemset3[] = $jml_itemset3;
                                $supportItemset3[] = $support3;
                            }
                        
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . $itemset1 . "</td>";
                            echo "<td>" . $itemset2 . "</td>";
                            echo "<td>" . $itemset3 . "</td>";
                            echo "<td>" . $jml_itemset3 . "</td>";
                            echo "<td>" . price_format($support3) . "</td>";
                            echo "<td>" . (($lolos==1)?"Lolos":"Tidak Lolos") . "</td>";
                            echo "</tr>";
                            $no++;
                        }
                    }
                }
            }
            $b++;
        }
        $a++;
    }
    echo "</table>";
    //insert into itemset3 one query with many value
    $value_insert_itemset3 = implode(",", $valueIn_itemset3);
    $sql_insert_itemset3 = "INSERT INTO itemset3(atribut1, atribut2, atribut3, jumlah, support, lolos, id_process) "
            . " VALUES ".$value_insert_itemset3;
    $db_object->db_query($sql_insert_itemset3);
    
    //display itemset yg lolos
    echo "<br><strong>Itemset 3 yang lolos:</strong><br>";
    echo "<table class='table table-bordered table-striped  table-hover'>
            <tr>
                <th>No</th>
                <th>Item 1</th>
                <th>Item 2</th>
                <th>Item 3</th>
                <th>Jumlah</th>
                <th>Suppport</th>
            </tr>";
    $no=1;
    foreach ($itemset3_var1 as $key => $value) {
        echo "<tr>";
        echo "<td>" . $no . "</td>";
        echo "<td>" . $value . "</td>";
        echo "<td>" . $itemset3_var2[$key] . "</td>";
        echo "<td>" . $itemset3_var3[$key] . "</td>";
        echo "<td>" . $jumlahItemset3[$key] . "</td>";
        echo "<td>" . price_format($supportItemset3[$key]) . "</td>";
        echo "</tr>";
        $no++;
    }
    echo "</table>";
    

    //hitung confidence
    $confidence_from_itemset = 0;
    //dari itemset 3 jika tidak ada yg lolos ambil dari itemset 2 jika tiak ada gagal mendapatkan confidence
    $sql_3 = "SELECT * FROM itemset3 WHERE lolos = 1 AND id_process = ".$id_process;
    $res_3 = $db_object->db_query($sql_3);
    $jumlah_itemset3_lolos = $db_object->db_num_rows($res_3);
    if($jumlah_itemset3_lolos > 0){
        $confidence_from_itemset = 3;
        
        while($row_3 = $db_object->db_fetch_array($res_3)){
            $atribut1 = $row_3['atribut1'];
            $atribut2 = $row_3['atribut2'];
            $atribut3 = $row_3['atribut3'];
            $supp_xuy = $row_3['support'];
            
            //1,2 => 3
            hitung_confidence($db_object, $supp_xuy, $min_support, $min_confidence, 
                    $atribut1, $atribut2, $atribut3, $id_process, $dataTransaksi, $jumlah_transaksi);
            
            //2,3 => 1
            hitung_confidence($db_object, $supp_xuy, $min_support, $min_confidence, 
                    $atribut2, $atribut3, $atribut1, $id_process, $dataTransaksi, $jumlah_transaksi);
            
            //3,1 => 2
            hitung_confidence($db_object, $supp_xuy, $min_support, $min_confidence, 
                    $atribut3, $atribut1, $atribut2, $id_process, $dataTransaksi, $jumlah_transaksi);
            
            
            //1 => 3,2
            hitung_confidence1($db_object, $supp_xuy, $min_support, $min_confidence, 
                    $atribut1, $atribut3, $atribut2, $id_process, $dataTransaksi, $jumlah_transaksi);
            
            //2 => 1,3
            hitung_confidence1($db_object, $supp_xuy, $min_support, $min_confidence,
                    $atribut2, $atribut1, $atribut3, $id_process, $dataTransaksi, $jumlah_transaksi);
            
            //3 => 2,1
            hitung_confidence1($db_object, $supp_xuy, $min_support, $min_confidence,
                    $atribut3, $atribut2, $atribut1, $id_process, $dataTransaksi, $jumlah_transaksi);
            
        }
    }

    //dari itemset 2
    $sql_2 = "SELECT * FROM itemset2 WHERE lolos = 1 AND id_process = ".$id_process;
    $res_2 = $db_object->db_query($sql_2);
    $jumlah_itemset2_lolos = $db_object->db_num_rows($res_2);
    if($jumlah_itemset2_lolos > 0){
        $confidence_from_itemset = 2;
        while($row_2 = $db_object->db_fetch_array($res_2)){
            $atribut1 = $row_2['atribut1'];
            $atribut2 = $row_2['atribut2'];
            $supp_xuy = $row_2['support'];
            
            //1 => 2
            hitung_confidence2($db_object, $supp_xuy, $min_support, $min_confidence, $atribut1, $atribut2, $id_process, $dataTransaksi, $jumlah_transaksi);
            
            //2 => 1
            hitung_confidence2($db_object, $supp_xuy, $min_support, $min_confidence, $atribut2, $atribut1, $id_process, $dataTransaksi, $jumlah_transaksi);
        }
    }

    if($confidence_from_itemset==0){
        return false;
    }

    return true;
}


function get_variasi_itemset3($array_itemset3, $item1, $item2, $item3, $item4) {
    $return = array();
    
    $return1 = array();
    if(!in_array(strtoupper($item1), array_map('strtoupper', $return1))){
        $return1[] = $item1;
    }
    if(!in_array(strtoupper($item2), array_map('strtoupper', $return1))){
        $return1[] = $item2;
    }
    if(!in_array(strtoupper($item3), array_map('strtoupper', $return1))){
        $return1[] = $item3;
    }
    
    $return2 = array();
    if(!in_array(strtoupper($item1), array_map('strtoupper', $return2))){
        $return2[] = $item1;
    }
    if(!in_array(strtoupper($item2), array_map('strtoupper', $return2))){
        $return2[] = $item2;
    }
    if(!in_array(strtoupper($item4), array_map('strtoupper', $return2))){
        $return2[] = $item4;
    }
    
    $return3 = array();
    if(!in_array(strtoupper($item1), array_map('strtoupper', $return3))){
        $return3[] = $item1;
    }
    if(!in_array(strtoupper($item3), array_map('strtoupper', $return3))){
        $return3[] = $item3;
    }
    if(!in_array(strtoupper($item4), array_map('strtoupper', $return3))){
        $return3[] = $item4;
    }
    
    $return4 = array();
    if(!in_array(strtoupper($item2), array_map('strtoupper', $return4))){
        $return4[] = $item2;
    }
    if(!in_array(strtoupper($item3), array_map('strtoupper', $return4))){
        $return4[] = $item3;
    }
    if(!in_array(strtoupper($item4), array_map('strtoupper', $return4))){
        $return4[] = $item4;
    }
    
    if(count($return1)==3){
        if(!is_exist_variasi_on_itemset3($return, $return1)){
            if(!is_exist_variasi_on_itemset3($array_itemset3, $return1)){
                $return[] = $return1;
            }
        }
    }
    if(count($return2)==3){
        if(!is_exist_variasi_on_itemset3($return, $return2)){
            if(!is_exist_variasi_on_itemset3($array_itemset3, $return2)){
                $return[] = $return2;
            }
        }
    }
    if(count($return3)==3){
        if(!is_exist_variasi_on_itemset3($return, $return3)){
            if(!is_exist_variasi_on_itemset3($array_itemset3, $return3)){
                $return[] = $return3;
            }
        }
    }
    if(count($return4)==3){
        if(!is_exist_variasi_on_itemset3($return, $return4)){
            if(!is_exist_variasi_on_itemset3($array_itemset3, $return4)){
                $return[] = $return4;
            }
        }
    }
    return $return;
}

function is_exist_variasi_on_itemset3($array, $tiga_variasi){
    $return = false;
    
    foreach ($array as $key => $value) {
        $jml=0;
        foreach ($value as $key1 => $val1) {
            foreach ($tiga_variasi as $key2 => $val2) {
                if(strtoupper($val1) == strtoupper($val2)){
                    $jml++;
                }
            }
        }
        if($jml==3){
            $return=true;
            break;
        }
    }
    
    return $return;
}


function get_count_itemset2($db_object, $atribut1, $atribut2, $start_date, $end_date) {
    $sql = "SELECT COUNT(transaction_date) AS jml, transaction_date 
            FROM transaksi 
            WHERE (produk='$atribut1' OR produk = '$atribut2') 
                AND transaction_date BETWEEN '$start_date' AND '$end_date' 
            GROUP BY transaction_date
            HAVING COUNT(transaction_date)=2";
    $result = $db_object->db_query($sql);
    $jml = $db_object->db_num_rows($result);
    return $jml;
}

function get_count_itemset3($db_object, $atribut1, $atribut2, $atribut3, $start_date, $end_date) {
    $sql = "SELECT COUNT(transaction_date) AS jml, transaction_date FROM transaksi 
            WHERE (produk='$atribut1' OR produk = '$atribut2'  OR produk = '$atribut3') 
                AND transaction_date BETWEEN '$start_date' AND '$end_date' 
            GROUP BY transaction_date
            HAVING COUNT(transaction_date)=3";
    $result = $db_object->db_query($sql);
    $jml = $db_object->db_num_rows($result);
    return $jml;
}


/**
 * kombinasi atibut1 U atribut2 => $atribut3
 * save to table confidence
 * @param type $db_object
 * @param type $supp_xuy
 * @param type $atribut1
 * @param type $atribut2
 * @param type $atribut3
 */
function hitung_confidence($db_object, $supp_xuy, $min_support, $min_confidence,
        $atribut1, $atribut2, $atribut3, $id_process, $dataTransaksi, $jumlah_transaksi){
    
//    $sql1_ = "SELECT support FROM itemset2 "
//            . " WHERE atribut1 = '".$atribut1."' "
//            . " AND atribut2 = '".$atribut2."' "
//            . " AND id_process = ".$id_process;
//    $res1_ = $db_object->db_query($sql1_);
//    while($row1_ = $db_object->db_fetch_array($res1_)){
    //hitung nilai support $nilai_support_x seperti di itemset2
    $jml_itemset2 = jumlah_itemset2($dataTransaksi, $atribut1, $atribut2);
    $nilai_support_x = ($jml_itemset2/$jumlah_transaksi) * 100;
    
        $kombinasi1 = $atribut1." , ".$atribut2;
        $kombinasi2 = $atribut3;
        $supp_x = $nilai_support_x;//$row1_['support'];
        $conf = ($supp_xuy/$supp_x)*100;
        //lolos seleksi min confidence itemset3
        $lolos = ($conf >= $min_confidence)? 1:0;
        
        //hitung korelasi lift
        $jumlah_kemunculanAB = jumlah_itemset3($dataTransaksi, $atribut1, $atribut2, $atribut3);
        $PAUB = $jumlah_kemunculanAB/$jumlah_transaksi;
        
        $jumlah_kemunculanA = jumlah_itemset2($dataTransaksi, $atribut1, $atribut2);
        $jumlah_kemunculanB = jumlah_itemset1($dataTransaksi, $atribut3);
        
        //$nilai_uji_lift = $PAUB / $jumlah_kemunculanA * $jumlah_kemunculanB;
        $nilai_uji_lift = $PAUB / (($jumlah_kemunculanA/$jumlah_transaksi) * ($jumlah_kemunculanB/$jumlah_transaksi));
        $korelasi_rule = ($nilai_uji_lift<1)?"korelasi negatif":"korelasi positif";
        if($nilai_uji_lift==1){
            $korelasi_rule = "tidak ada korelasi";
        }
        
        //masukkan ke table confidence
        $db_object->insert_record("confidence", 
                array("kombinasi1" => $kombinasi1,
                    "kombinasi2" => $kombinasi2,
                    "support_xUy" => $supp_xuy,
                    "support_x" => $supp_x,
                    "confidence" => $conf,
                    "lolos" => $lolos,
                    "min_support" => $min_support,
                    "min_confidence" => $min_confidence,
                    "nilai_uji_lift" => $nilai_uji_lift,
                    "korelasi_rule" => $korelasi_rule,
                    "id_process" => $id_process,
                    "jumlah_a" => $jumlah_kemunculanA,
                    "jumlah_b" => $jumlah_kemunculanB,
                    "jumlah_ab" => $jumlah_kemunculanAB,
                    "px" => ($jumlah_kemunculanA/$jumlah_transaksi),
                    "py" => ($jumlah_kemunculanB/$jumlah_transaksi),
                    "pxuy" => $PAUB,
                    "from_itemset"=>3
                ));
//    }
}


/**
 * confidence atribut1 => atribut2 U atribut3
 * @param type $db_object
 * @param type $supp_xuy
 * @param type $min_support
 * @param type $min_confidence
 * @param type $atribut1
 * @param type $atribut2
 * @param type $atribut3
 */
function hitung_confidence1($db_object, $supp_xuy, $min_support, $min_confidence,
        $atribut1, $atribut2, $atribut3, $id_process, $dataTransaksi, $jumlah_transaksi){
    
//        $sql4_ = "SELECT support FROM itemset1 "
//                . " WHERE atribut = '".$atribut1."' "
//                . " AND id_process = ".$id_process;
//        $res4_ = $db_object->db_query($sql4_);
//        while($row4_ = $db_object->db_fetch_array($res4_)){
        //hitung nilai support seperti itemset1
    $jml_itemset1 = jumlah_itemset1($dataTransaksi, $atribut1);
    $nilai_support_x = ($jml_itemset1/$jumlah_transaksi) * 100;
    
            $kombinasi1 = $atribut1;
            $kombinasi2 = $atribut2." , ".$atribut3;
            $supp_x = $nilai_support_x;//$row4_['support'];
            $conf = ($supp_xuy/$supp_x)*100;
            //lolos seleksi min confidence itemset3
            $lolos = ($conf >= $min_confidence)? 1:0;
            
            //hitung korelasi lift
            $jumlah_kemunculanAB = jumlah_itemset3($dataTransaksi, $atribut1, $atribut2, $atribut3);
            $PAUB = $jumlah_kemunculanAB/$jumlah_transaksi;

            $jumlah_kemunculanA = jumlah_itemset1($dataTransaksi, $atribut1);
            $jumlah_kemunculanB = jumlah_itemset2($dataTransaksi, $atribut2, $atribut3);

            $nilai_uji_lift = $PAUB / (($jumlah_kemunculanA/$jumlah_transaksi) * ($jumlah_kemunculanB/$jumlah_transaksi));
            $korelasi_rule = ($nilai_uji_lift<1)?"korelasi negatif":"korelasi positif";
            if($nilai_uji_lift==1){
                $korelasi_rule = "tidak ada korelasi";
            }
        
        
            //masukkan ke table confidence
            $db_object->insert_record("confidence", 
                    array("kombinasi1" => $kombinasi1,
                        "kombinasi2" => $kombinasi2,
                        "support_xUy" => $supp_xuy,
                        "support_x" => $supp_x,
                        "confidence" => $conf,
                        "lolos" => $lolos,
                        "min_support" => $min_support,
                        "min_confidence" => $min_confidence,
                        "nilai_uji_lift" => $nilai_uji_lift,
                        "korelasi_rule" => $korelasi_rule,
                        "id_process" => $id_process,
                        "jumlah_a" => $jumlah_kemunculanA,
                        "jumlah_b" => $jumlah_kemunculanB,
                        "jumlah_ab" => $jumlah_kemunculanAB,
                        "px" => ($jumlah_kemunculanA/$jumlah_transaksi),
                        "py" => ($jumlah_kemunculanB/$jumlah_transaksi),
                        "pxuy" => $PAUB,
                        "from_itemset"=>3
                    ));
//        }
}


function hitung_confidence2($db_object, $supp_xuy, $min_support, $min_confidence,
        $atribut1, $atribut2, $id_process, $dataTransaksi, $jumlah_transaksi){
    
//        $sql1_ = "SELECT support FROM itemset1 "
//                    . " WHERE atribut = '".$atribut1."' AND id_process = ".$id_process;
//        $res1_ = $db_object->db_query($sql1_);
//        while($row1_ = $db_object->db_fetch_array($res1_)){
        //hitung nilai support seperti itemset1
        $jml_itemset1 = jumlah_itemset1($dataTransaksi, $atribut1);
        $nilai_support_x = ($jml_itemset1/$jumlah_transaksi) * 100;
    
            $kombinasi1 = $atribut1;
            $kombinasi2 = $atribut2;
            $supp_x = $nilai_support_x;//$row1_['support'];
            $conf = ($supp_xuy/$supp_x)*100;
            //lolos seleksi min confidence itemset3
            $lolos = ($conf >= $min_confidence)? 1:0;
            
            //hitung korelasi lift
            $jumlah_kemunculanAB = jumlah_itemset2($dataTransaksi, $atribut1, $atribut2);
            $PAUB = $jumlah_kemunculanAB/$jumlah_transaksi;

            $jumlah_kemunculanA = jumlah_itemset1($dataTransaksi, $atribut1);
            $jumlah_kemunculanB = jumlah_itemset1($dataTransaksi, $atribut2);

            $nilai_uji_lift = $PAUB / (($jumlah_kemunculanA/$jumlah_transaksi) * ($jumlah_kemunculanB/$jumlah_transaksi));
            $korelasi_rule = ($nilai_uji_lift<1)?"korelasi negatif":"korelasi positif";
            if($nilai_uji_lift==1){
                $korelasi_rule = "tidak ada korelasi";
            }
            
            //masukkan ke table confidence
            $db_object->insert_record("confidence", 
                    array("kombinasi1" => $kombinasi1,
                        "kombinasi2" => $kombinasi2,
                        "support_xUy" => $supp_xuy,
                        "support_x" => $supp_x,
                        "confidence" => $conf,
                        "lolos" => $lolos,
                        "min_support" => $min_support,
                        "min_confidence" => $min_confidence,
                        "nilai_uji_lift" => $nilai_uji_lift,
                        "korelasi_rule" => $korelasi_rule,
                        "id_process" => $id_process,
                        "jumlah_a" => $jumlah_kemunculanA,
                        "jumlah_b" => $jumlah_kemunculanB,
                        "jumlah_ab" => $jumlah_kemunculanAB,
                        "px" => ($jumlah_kemunculanA/$jumlah_transaksi),
                        "py" => ($jumlah_kemunculanB/$jumlah_transaksi),
                        "pxuy" => $PAUB,
                        "from_itemset"=>2
                    ));
//        }
}


function jumlah_itemset1($transaksi_list, $produk){
    $count = 0;
    foreach ($transaksi_list as $key => $data) {
        $items = ",".strtoupper($data['produk']);
        $item_cocok = ",".strtoupper($produk).",";
        $pos = strpos($items, $item_cocok);
        if($pos!==false){//was found at position $pos
            $count++;
        }
    }
    return $count;
}

function jumlah_itemset2($transaksi_list, $variasi1, $variasi2){
    $count = 0;
    foreach ($transaksi_list as $key => $data) {
        $items = ",".strtoupper($data['produk']);
        $item_variasi1 = ",".strtoupper($variasi1).",";
        $item_variasi2 = ",".strtoupper($variasi2).",";
        
        $pos1 = strpos($items, $item_variasi1);
        $pos2 = strpos($items, $item_variasi2);
        if($pos1!==false && $pos2!==false){//was found at position $pos
            $count++;
        }
    }
    return $count;
}

function jumlah_itemset3($transaksi_list, $variasi1, $variasi2, $variasi3){
    $count = 0;
    foreach ($transaksi_list as $key => $data) {
        $items = ",".strtoupper($data['produk']);
        $item_variasi1 = ",".strtoupper($variasi1).",";
        $item_variasi2 = ",".strtoupper($variasi2).",";
        $item_variasi3 = ",".strtoupper($variasi3).",";
        
        $pos1 = strpos($items, $item_variasi1);
        $pos2 = strpos($items, $item_variasi2);
        $pos3 = strpos($items, $item_variasi3);
        if($pos1!==false && $pos2!==false && $pos3!==false){//was found at position $pos
            $count++;
        }
    }
    return $count;
}


