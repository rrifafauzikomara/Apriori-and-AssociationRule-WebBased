<?php

function display_process_hasil_mining($db_object, $id_process) {
//    ?>
<!--    <strong>Itemset 1:</strong>
    <table class = 'table table-bordered table-striped  table-hover'>
    <tr>
    <th>No</th>
    <th>Item</th>
    <th>Jumlah</th>
    <th>Support</th>
    <th></th>
    </tr>-->
    <?php
//    $sql1 = "SELECT * FROM itemset1 "
//            . " WHERE id_process = ".$id_process
//            . " ORDER BY lolos DESC";
//    $query1 = $db_object->db_query($sql1);
//    $no = 1;
//    while ($row1 = $db_object->db_fetch_array($query1)) {
//        echo "<tr>";
//        echo "<td>" . $no . "</td>";
//        echo "<td>" . $row1['atribut'] . "</td>";
//        echo "<td>" . $row1['jumlah'] . "</td>";
//        echo "<td>" . $row1['support'] . "</td>";
//        echo "<td>" . ($row1['lolos'] == 1 ? "Lolos" : "Tidak Lolos") . "</td>";
//        echo "</tr>";
//        $no++;
//    }
//    ?>
    <!--</table>-->


<!--    <strong>Itemset 2:</strong>
    <table class='table table-bordered table-striped  table-hover'>
        <tr>
            <th>No</th>
            <th>Item 1</th>
            <th>Item 2</th>
            <th>Jumlah</th>
            <th>Support</th>
            <th></th>
        </tr>-->
        <?php
//        $sql1 = "SELECT * FROM itemset2 "
//                . " WHERE id_process = ".$id_process
//                . " ORDER BY lolos DESC";
//        $query1 = $db_object->db_query($sql1);
//        $no = 1;
//        while ($row1 = $db_object->db_fetch_array($query1)) {
//            echo "<tr>";
//            echo "<td>" . $no . "</td>";
//            echo "<td>" . $row1['atribut1'] . "</td>";
//            echo "<td>" . $row1['atribut2'] . "</td>";
//            echo "<td>" . $row1['jumlah'] . "</td>";
//            echo "<td>" . $row1['support'] . "</td>";
//            echo "<td>" . ($row1['lolos'] == 1 ? "Lolos" : "Tidak Lolos") . "</td>";
//            echo "</tr>";
//            $no++;
//        }
//        ?>
<!--    </table>

    <strong>Itemset 3:</strong>
    <table class='table table-bordered table-striped  table-hover'>
        <tr>
            <th>No</th>
            <th>Item 1</th>
            <th>Item 2</th>
            <th>Item 3</th>
            <th>Jumlah</th>
            <th>Support</th>
            <th></th>
        </tr>-->
        <?php
//        $sql1 = "SELECT * FROM itemset3 "
//                . " WHERE id_process = ".$id_process
//                . " ORDER BY lolos DESC";
//        $query1 = $db_object->db_query($sql1);
//        $no = 1;
//        while ($row1 = $db_object->db_fetch_array($query1)) {
//            echo "<tr>";
//            echo "<td>" . $no . "</td>";
//            echo "<td>" . $row1['atribut1'] . "</td>";
//            echo "<td>" . $row1['atribut2'] . "</td>";
//            echo "<td>" . $row1['atribut3'] . "</td>";
//            echo "<td>" . $row1['jumlah'] . "</td>";
//            echo "<td>" . $row1['support'] . "</td>";
//            echo "<td>" . ($row1['lolos'] == 1 ? "Lolos" : "Tidak Lolos") . "</td>";
//            echo "</tr>";
//            $no++;
//        }
//        ?>
    <!--</table>-->

    <?php
    $sql1 = "SELECT * FROM confidence "
                . " WHERE id_process = ".$id_process
                . " AND from_itemset=3 "
                ;//. " ORDER BY lolos DESC";
    $query1 = $db_object->db_query($sql1);
    ?>
    Confidence dari itemset 3
    <table class='table table-bordered table-striped  table-hover'>
        <tr>
        <th>No</th>
        <th>X => Y</th>
        <th>Support X U Y</th>
        <th>Support X </th>
        <th>Confidence</th>
        <th>Keterangan</th>
        </tr>
        <?php
            $no=1;
            $data_confidence = array();
            while($row=$db_object->db_fetch_array($query1)){
                    echo "<tr>";
                    echo "<td>".$no."</td>";
                    echo "<td>".$row['kombinasi1']." => ".$row['kombinasi2']."</td>";
                    echo "<td>".price_format($row['support_xUy'])."</td>";
                    echo "<td>".price_format($row['support_x'])."</td>";
                    echo "<td>".price_format($row['confidence'])."</td>";
                    $keterangan = ($row['confidence'] <= $row['min_confidence'])?"Tidak Lolos":"Lolos";
                    echo "<td>".$keterangan."</td>";
                echo "</tr>";
                $no++;
                if($row['lolos']==1){
                $data_confidence[] = $row;
                }
            }
            ?>
    </table>
    
    
    <?php
    $sql1 = "SELECT * FROM confidence "
                . " WHERE id_process = ".$id_process
                . " AND from_itemset=2 "
                ;//. " ORDER BY lolos DESC";
    $query1 = $db_object->db_query($sql1);
    ?>
    Confidence dari itemset 2
    <table class='table table-bordered table-striped  table-hover'>
        <tr>
        <th>No</th>
        <th>X => Y</th>
        <th>Support X U Y</th>
        <th>Support X </th>
        <th>Confidence</th>
        <th>Keterangan</th>
        </tr>
        <?php
            $no=1;
            //$data_confidence = array();
            while($row=$db_object->db_fetch_array($query1)){
                    echo "<tr>";
                    echo "<td>".$no."</td>";
                    echo "<td>".$row['kombinasi1']." => ".$row['kombinasi2']."</td>";
                    echo "<td>".price_format($row['support_xUy'])."</td>";
                    echo "<td>".price_format($row['support_x'])."</td>";
                    echo "<td>".price_format($row['confidence'])."</td>";
                    $keterangan = ($row['confidence'] <= $row['min_confidence'])?"Tidak Lolos":"Lolos";
                    echo "<td>".$keterangan."</td>";
                echo "</tr>";
                $no++;
                if($row['lolos']==1){
                $data_confidence[] = $row;
                }
            }
            ?>
    </table>

    <strong>Rule Asosiasi yang terbentuk:</strong>
    <table class='table table-bordered table-striped  table-hover'>
        <tr>
            <th>No</th>
            <th>X => Y</th>
            <th>Confidence</th>
            <th>Nilai Uji lift</th>
            <th>Korelasi rule</th>
            <!-- <th></th> -->
        </tr>
        <?php
        
        $no = 1;
        //while ($row1 = $db_object->db_fetch_array($query1)) {
        foreach($data_confidence as $key => $val){
//            $kom1 = explode(" , ", $row1['kombinasi1']);
//            $jika = implode(" Dan ", $kom1);
//            $kom2 = explode(" , ", $row1['kombinasi2']);
//            $maka = implode(" Dan ", $kom2);
            echo "<tr>";
            echo "<td>" . $no . "</td>";
            echo "<td>" . $val['kombinasi1']." => ".$val['kombinasi2'] . "</td>";
            echo "<td>" . price_format($val['confidence']) . "</td>";
            echo "<td>" . price_format($val['nilai_uji_lift']) . "</td>";
            echo "<td>" . ($val['korelasi_rule']) . "</td>";
            //echo "<td>" . ($val['lolos'] == 1 ? "Lolos" : "Tidak Lolos") . "</td>";
            echo "</tr>";
            $no++;
        }
        ?>
    </table>

    <?php
}
?>