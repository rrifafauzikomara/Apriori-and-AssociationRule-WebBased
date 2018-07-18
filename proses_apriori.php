<?php
//session_start();
if (!isset($_SESSION['apriori_parfum_id'])) {
    header("location:index.php?menu=forbidden");
}

include_once "database.php";
include_once "fungsi.php";
include_once "mining.php";
include_once "display_mining.php";
?>
<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            <div class="page-header">
                <h1>
                    Proses Apriori
                </h1>
            </div><!-- /.page-header -->
            
<?php
//object database class
$db_object = new database();

$pesan_error = $pesan_success = "";
if (isset($_GET['pesan_error'])) {
    $pesan_error = $_GET['pesan_error'];
}
if (isset($_GET['pesan_success'])) {
    $pesan_success = $_GET['pesan_success'];
}

if (isset($_POST['submit'])) {
    $can_process = true;
    if (empty($_POST['min_support']) || empty($_POST['min_confidence'])) {
        $can_process = false;
        ?>
        <script> location.replace("?menu=proses_apriori&pesan_error=Min Support dan Min Confidence harus diisi");</script>
        <?php
    }
    if(!is_numeric($_POST['min_support']) || !is_numeric($_POST['min_confidence'])){
        $can_process = false;
        ?>
        <script> location.replace("?menu=proses_apriori&pesan_error=Min Support dan Min Confidence harus diisi angka");</script>
        <?php
    }
    //  01/09/2016 - 30/09/2016

    if($can_process){
        $tgl = explode(" - ", $_POST['range_tanggal']);
        $start = format_date($tgl[0]);
        $end = format_date($tgl[1]);

        if(isset($_POST['id_process'])){
            $id_process = $_POST['id_process'];
            //delete hitungan untuk id_process
            reset_hitungan($db_object, $id_process);

            //update log process
            $field = array(
                            "start_date"=>$start,
                            "end_date"=>$end,
                            "min_support"=>$_POST['min_support'],
                            "min_confidence"=>$_POST['min_confidence']
                        );
            $where = array(
                            "id"=>$id_process
                        );
            $query = $db_object->update_record("process_log", $field, $where);
        }
        else{
            //insert log process
            $field_value = array(
                            "start_date"=>$start,
                            "end_date"=>$end,
                            "min_support"=>$_POST['min_support'],
                            "min_confidence"=>$_POST['min_confidence']
                        );
            $query = $db_object->insert_record("process_log", $field_value);
            $id_process = $db_object->db_insert_id();
        }
        //show form for update
        ?>
        <div class="row">
            <div class="col-sm-12">
            
                <form method="post" action="">
                    <div class="col-lg-6 " >
                        <!-- Date range -->
                        <div class="form-group">
                            <label>Tanggal: </label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right" name="range_tanggal"
                                       id="id-date-range-picker-1" required="" placeholder="Date range" 
                                       value="<?php echo $_POST['range_tanggal']; ?>">
                            </div><!-- /.input group -->
                        </div><!-- /.form group -->
                        <div class="form-group">
                            <input name="search_display" type="submit" value="Search" class="btn btn-default">
                        </div>
                    </div>
                    <div class="col-lg-6 " >
                        <div class="form-group">
                            <label>Min Support: </label>
                            <input name="min_support" type="text" 
                                   value="<?php echo $_POST['min_support']; ?>"
                                   class="form-control" placeholder="Min Support">
                        </div>
                        <div class="form-group">
                            <label>Min Confidence: </label>
                            <input name="min_confidence" type="text"
                                   value="<?php echo $_POST['min_confidence']; ?>"
                                   class="form-control" placeholder="Min Confidence">
                        </div>
                        <input type="hidden" name="id_process" value="<?php echo $id_process; ?>">
                        <div class="form-group">
                            <input name="submit" type="submit" value="Proses" class="btn btn-success">
                        </div>
                    </div>

                </form>
            </div>
        </div>
        <?php


        echo "Min Support Absolut: " . $_POST['min_support'];
        echo "<br>";
        $sql = "SELECT COUNT(*) FROM transaksi 
        WHERE transaction_date BETWEEN '$start' AND '$end' ";
        $res = $db_object->db_query($sql);
        $num = $db_object->db_fetch_array($res);
        $minSupportRelatif = ($_POST['min_support']/$num[0]) * 100;
        echo "Min Support Relatif: " . $minSupportRelatif;
        echo "<br>";
        echo "Min Confidence: " . $_POST['min_confidence'];
        echo "<br>";
        echo "Start Date: " . $_POST['range_tanggal'];
        echo "<br>";

        $result = mining_process($db_object, $_POST['min_support'], $_POST['min_confidence'],
                $start, $end, $id_process);
        if ($result) {
            display_success("Proses mining selesai");
        } else {
            display_error("Gagal mendapatkan aturan asosiasi");
        }

        display_process_hasil_mining($db_object, $id_process);
    }
               
} 
else {
    $where = "ga gal";
    if(isset($_POST['range_tanggal'])){
        $tgl = explode(" - ", $_POST['range_tanggal']);
        $start = format_date($tgl[0]);
        $end = format_date($tgl[1]);
        
        $where = " WHERE transaction_date "
                . " BETWEEN '$start' AND '$end'";
    }
    $sql = "SELECT
        *
        FROM
         transaksi ".$where;
    
    $query = $db_object->db_query($sql);
    $jumlah = $db_object->db_num_rows($query);
    ?>
    <form method="post" action="">
        <div class="row">
            <div class="col-lg-6 " >
                <!-- Date range -->
                <div class="form-group">
                    <label>Tanggal: </label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <input type="text" class="form-control pull-right" name="range_tanggal"
                               id="id-date-range-picker-1" required="" placeholder="Date range" 
                               value="<?php echo $_POST['range_tanggal']; ?>">
                    </div><!-- /.input group -->
                </div><!-- /.form group -->


                <div class="form-group">
                    <input name="search_display" type="submit" value="Search" class="btn btn-default">
                </div>
            </div>
            <div class="col-lg-6 " >
                <div class="form-group">
                    <input name="min_support" type="text" class="form-control" placeholder="Min Support">
                </div>
                <div class="form-group">
                    <input name="min_confidence" type="text" class="form-control" placeholder="Min Confidence">
                </div>
                <div class="form-group">
                    <input name="submit" type="submit" value="Proses" class="btn btn-success">
                </div>
            </div>

        </div>
    </form>

    <?php
    if (!empty($pesan_error)) {
        display_error($pesan_error);
    }
    if (!empty($pesan_success)) {
        display_success($pesan_success);
    }


    echo "Jumlah data: " . $jumlah . "<br>";
    if ($jumlah == 0) {
        echo "Data kosong...";
    } 
    else {
        ?>
        <table class='table table-bordered table-striped  table-hover'>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Produk</th>
            </tr>
            <?php
            $no = 1;
            while ($row = $db_object->db_fetch_array($query)) {
                echo "<tr>";
                echo "<td>" . $no . "</td>";
                echo "<td>" . $row['transaction_date'] . "</td>";
                echo "<td>" . $row['produk'] . "</td>";
                echo "</tr>";
                $no++;
            }
            ?>
        </table>
        <?php
    }           
}
?>
        </div>
    </div>
</div>

       