<?php

function can_access_menu($menu){
    if($_SESSION['apriori_parfum_level']==2 & ($menu=='hasil_rule' || $menu=='view_rule')){
        return true;
    }
    if($_SESSION['apriori_parfum_level']==1){
        return true;
    }
    return false;
}
//START USERS===================================================================
/**
 * combobox list users dengan text didepannya (for input form)
 * @param string $label default=''
 * @param string $name default=''
 * @param string $value default=''
 * @param boolean $required default=false
 * @param boolean $all default=false
 * @param string $text_all default='-' tulisan dipilihan all
 * @param string $where default='' kondisi query, tuisan where sudah ada tingggal tambahkan sendiri...
 * @param string $params defaulr=''
 */
function text_with_list_users($label='', $name='', $value='', $required=false, $all=false, $text_all='-',$where='', $params='')
{
//	start_row();
//            start_column("width='10%'");
                    label($label);
//            end_column();
//            start_column();
                    list_users($name, $value, $required, $all, $text_all, $where, $params);
//            end_column();
//	end_row();
}

/**
 * data list users (from db: users)
 * @param string $name
 * @param string $selected_id default=''
 * @param boolean $required default=false
 * @param boolean $all default=false
 * @param string $text_all default='-' tulisan dipilihan all
 * @param string $where default='' kondisi query, tuisan where sudah ada tingggal tambahkan sendiri...
 * @param string $params default=''
 */
function list_users($name, $selected_id='', $required=false, $all=false, $text_all='-', $where='', $params=''){
        $db_obj = new database();
        
	$table = TB_PREF."users";
        $query = $db_obj->display_table_all_column($table, $where, false, false, 0, 0, 'nama');
	echo "<select name='$name' "; if($required) echo "required='required'"; echo " class='form-control' ".$params." >";
        //pilihan semua / kosong
        if($all){                
            echo "<option value=''> $text_all </option>";
        }
	
        //loop data
        while($myrow = $db_obj->db_fetch_array($query)){	
            echo "<option value='".$myrow['id']."' "; if($selected_id==$myrow['id']) echo "selected='selected'";
            echo ">".$myrow['user_id']." -> ".$myrow['nama']."</option>";		
        }
	echo "</select>";
}
//END USERS===================================================================

//START LEVEL USERS===================================================================
/**
 * combobox list level user dengan text didepannya (for input form)
 * @param string $label default=''
 * @param string $name default=''
 * @param string $value default=''
 * @param boolean $required default=false
 * @param boolean $all default=false
 * @param string $text_all default='-' tulisan dipilihan all
 * @param string $where default='' kondisi query, tuisan where sudah ada tingggal tambahkan sendiri...
 * @param string $params default=''
 */
function text_with_list_levels($label='', $name='', $value='', $required=false, $all=false, $text_all='-',$where='', $params='')
{
//	start_row();
//            start_column("width='10%'");
                    label($label);
//            end_column();
//            start_column();
                list_levels($name, $value, $required, $all, $text_all, $where, $params);
//            end_column();
//	end_row();
}


/**
 * data list levels (level akses user)
 * @param string $name
 * @param string $selected_id default=''
 * @param boolean $required default=false
 * @param boolean $all default=false
 * @param string $text_all default='-' tulisan dipilihan all
 * @param string $where default='' kondisi query, tuisan where sudah ada tingggal tambahkan sendiri...
 * @param string $params default=''
 */
function list_levels($name, $selected_id='', $required=false, $all=false, $text_all='-', $where='', $params=''){
        
	echo "<select name='$name' "; if($required) echo "required='required'"; echo " class='form-control' $params >";
        //pilihan semua / kosong
        if($all){                
            echo "<option value=''> $text_all </option>";
        }
	
        echo "<option value='1' ";
            if($selected_id=="1") echo "selected='selected' >Admin</option>";
        echo "<option value='2' ";
            if($selected_id=="2") echo "selected='selected' >User Store</option>";
            
	echo "</select>";
}
//END LEVEL USERS===================================================================
function list_periode($name, $selected_id='', $required=false, $all=false, $text_all='-', $where='', $params=''){
        $db_obj = new database();
        
    $table = "periode";
        $query = $db_obj->display_table_all_column($table, $where, false, false, 0, 0, 'id_periode');
    echo "<select name='$name' "; if($required) echo "required='required'"; echo " class='form-control' ".$params." >";
        //pilihan semua / kosong
        if($all){                
            echo "<option value=''> $text_all </option>";
        }
    
        //loop data
        while($myrow = $db_obj->db_fetch_array($query)){    
            echo "<option value='".$myrow['id_periode']."' "; if($selected_id==$myrow['id_periode']) echo "selected='selected'";
            echo ">".$myrow['semester']." - ".$myrow['tahun']."</option>";       
        }
    echo "</select>";
}

function list_mata_kuliah($name, $selected_id='', $required=false, $all=false, $text_all='-', $where='', $params=''){
        $db_obj = new database();
        
    $table = "mata_kuliah";
        $query = $db_obj->display_table_all_column($table, $where, false, false, 0, 0, 'id_mk');
    echo "<select name='$name' "; if($required) echo "required='required'"; echo " class='form-control' ".$params." >";
        //pilihan semua / kosong
        if($all){                
            echo "<option value=''> $text_all </option>";
        }
    
        //loop data
        while($myrow = $db_obj->db_fetch_array($query)){    
            echo "<option value='".$myrow['id_mk']."' "; if($selected_id==$myrow['id_mk']) echo "selected='selected'";
            echo ">".$myrow['n_mk']."</option>";       
        }
    echo "</select>";
}


function list_dosen($name, $selected_id='', $required=false, $all=false, $text_all='-', $where='', $params=''){
        $db_obj = new database();
        
    $table = "dosen";
        $query = $db_obj->display_table_all_column($table, $where, false, false, 0, 0, 'id_dosen');
    echo "<select name='$name' "; if($required) echo "required='required'"; echo " class='form-control' ".$params." >";
        //pilihan semua / kosong
        if($all){                
            echo "<option value=''> $text_all </option>";
        }
    
        //loop data
        while($myrow = $db_obj->db_fetch_array($query)){    
            echo "<option value='".$myrow['id_dosen']."' "; if($selected_id==$myrow['id_dosen']) echo "selected='selected'";
            echo ">".$myrow['nama']."</option>";       
        }
    echo "</select>";
}


function list_bulan($selected_id){
	echo "<select name='bulan' class='err'>";
	echo "	<option value=''>--Bulan--</option>";
	echo "	<option value='01' "; if($selected_id=='01') echo "selected='selected'"; echo ">Januari</option>";
	echo "	<option value='02' "; if($selected_id=='02') echo "selected='selected'"; echo ">Februari</option>";
	echo "	<option value='03' "; if($selected_id=='03') echo "selected='selected'"; echo ">Maret</option>";
	echo "	<option value='04' "; if($selected_id=='04') echo "selected='selected'"; echo ">April</option>";
	echo "	<option value='05' "; if($selected_id=='05') echo "selected='selected'"; echo ">Mei</option>";
	echo "	<option value='06' "; if($selected_id=='06') echo "selected='selected'"; echo ">Juni</option>";
	echo "	<option value='07' "; if($selected_id=='07') echo "selected='selected'"; echo ">Juli</option>";
	echo "	<option value='08' "; if($selected_id=='08') echo "selected='selected'"; echo ">Agustus</option>";
	echo "	<option value='09' "; if($selected_id=='09') echo "selected='selected'"; echo ">September</option>";
	echo "	<option value='10' "; if($selected_id=='10') echo "selected='selected'"; echo ">Oktober</option>";
	echo "	<option value='11' "; if($selected_id=='11') echo "selected='selected'"; echo ">November</option>";
	echo "	<option value='12' "; if($selected_id=='12') echo "selected='selected'"; echo ">Desember</option>";	
	echo "</select>";
}

function list_numbers($name, $selected_id='', $required=false, $all=false, $text_all='-', $count=5, $params=''){
        
	echo "<select name='$name' "; 
            echo ($required==true)?"required='required'":""; 
            echo "$params style='width: 100%;'>";
        //pilihan semua / kosong
        if($all){                
            echo "<option value=''> $text_all </option>";
        }
	
        //loop data
        $no = 1;
        while($no <= $count){
            echo "<option value='".$no."' "; 
            echo ($selected_id==$no)?"selected='selected'":"";
            echo ">".$no."</option>";	
            $no++;
        }
	echo "</select>";
}

//END LIST-LIST/COMBOBOX
//---------------------------------------------------------------------------------
/**
 * alert popup javascript
 * @param string $msg pesan
 */
function phpAlert($msg){
    echo '<script type="text/javascript">alert("' . $msg . '")</script>';
}

/**
 * notifikasi error (merah)
 * @param string $msg pesan
 */
function display_error($msg){
    echo "<div class='alert alert-danger alert-dismissable'>
            
            <h4><i class='icon fa fa-ban'></i> Error! </h4>
            ".$msg."
        </div>";
}

/**
 * notifikasi warning (kuning)
 * @param string $msg pesan
 */
function display_warning($msg){
    echo "<div class='alert alert-warning alert-dismissable'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
            <h4><i class='icon fa fa-warning'></i> Warning! </h4>
            ".$msg."
          </div>";
}

/**
 * notifikasi informasi (biru)
 * @param string $msg pesan
 */
function display_information($msg){
    echo "<div class='alert alert-info alert-dismissable'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
            <h4><i class='icon fa fa-info'></i> Information </h4>
            ".$msg."
          </div>";
}

/**
 * notifikasi sukses (hijau)
 * @param string $msg pesan
 */
function display_success($msg){
    echo "<div class='alert alert-success alert-dismissable'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                    <h4><i class='icon fa fa-check'></i> Success. </h4>
                    ".$msg."
                  </div>";
}

/**
 * enter br with looping
 * @param int $a default=1
 */
function br($a=1){
	if($a>=1){
		$aa=0;
		while($aa<=$a){
			echo "<br>";
			$aa++;
		}
	}
}

/**
 * spasi &nbsp; with looping
 * @param int $a default=1
 */
function space($a=1){
	if($a>=1){
		$aa=0;
		while($aa<=$a){
			echo "&nbsp;";
			$aa++;
		}
	}
}

/**
 * start div
 * @param string $params default=''
 */
function start_div($params=''){
	echo "<div $params>";
}

/**
 * end div
 */
function end_div(){
	echo "</div>";
}

/**
 * start form
 * @param string $params default=''
 */
function start_form($params=''){
	echo "<form action='' method='post' $params>";
}

/**
 * end form
 */
function end_form(){
	echo "</form>";
}

/**
 * table start
 * @param string $params default=''
 */
function start_table($params=''){
	echo "<table $params>";
}

/**
 * end table
 */
function end_table(){
	echo "</table>";
}

/**
 * echo tr $params;
 * @param string $params default=''
 */
function start_row($params=''){
	echo "<tr $params>";
}

/**
 * echo /tr
 */
function end_row(){
	echo "</tr>";
}

/**
 * echo td $params;
 * @param string $params default=''
 */
function start_column($params=''){
	echo "<td $params>";
}

/**
 * echo /td
 */
function end_column(){
	echo "</td>";
}


/**
 * label text
 * @param string $label default=''
 * @param type $params parameter tambahan default=''
 */
function label($label='', $params=''){
	echo "<label for='name' $params >".$label;//.<!-- <span class="red">(required)</span> --></label>"
	echo "</label>";
}

/**
 * input text area
 * @param string $name
 * @param string $value
 * @param boolean $required
 * @param string $params default=''
 * @param boolean $texteditor text area dengan tinymce default=false
 */
function input_text_area($name,$value, $required=false, $params='', $texteditor=false){
    $tinymce = "mceNoEditor";
    if($texteditor){
        $tinymce = "mceEditor";
    }
    if(!$required){
        echo "<textarea name='$name' rows='10' cols='80' $params>".$value."</textarea>";
    }
    else{
        echo "<textarea name='$name' required='required' class='form-control $tinymce' $params>".$value."</textarea>";
    }
}

/**
 * input text area with label
 * @param type $label
 * @param type $name
 * @param type $value
 * @param type $required
 * @param type $params
 * @param boolean $texteditor textarea dengan tinymce default=false
 */
function text_area($label='', $name='', $value=null, $required=false, $params='', $texteditor=false)
{
    //template bootstrap tanpa row and column
//	start_row();
//		start_column("width='10%'");
			label($label);
//		end_column();
//		start_column();
			input_text_area($name, $value, $required, $params, $texteditor);
//		end_column();
//	end_row();
}

/**
 * password field one row (for form input)
 * @param string $label default=''
 * @param string $name default=''
 * @param string $value default=''
 * @param boolean $required default=false
 * @param string $params default=''
 */
function password_field($label='', $name='', $value='', $required=false, $params='')
{
    //template bootstrap tanpa row and column
//	start_row();
//		start_column("width='10%'");
			label($label);
//		end_column();
//		start_column();
			input_password_text($name, $value, $required, $params);
//		end_column();
//	end_row();
}

/**
 * text field one row (for form input)
 * @param string $label default=''
 * @param string $name default=''
 * @param string $value default=''
 * @param boolean $required default=false
 * @param string $params default=''
 */
function text_field($label='', $name='', $value='', $required=false, $params='')
{
    //template bootstrap tanpa row and column
//	start_row();
//		start_column("width='10%'");
			label($label);
//		end_column();
//		start_column();
			input_free_text($name, $value, $required, $params);
//		end_column();
//	end_row();
}

/**
 * text input tipe file
 * @param type $label
 * @param type $name
 * @param type $required
 * @param type $params
 */
function text_input_file($label='', $name='', $required=false, $params=''){
    label($label);
    input_file($name, $required, $params );
}

/**
 * amount field one row (for form input) harga
 * @param string $label default=''
 * @param string $name default=''
 * @param string $value default=''
 * @param boolean $required default=false
 * @param string $params default=''
 */
function amount_field($label='', $name='', $value='', $required=false, $params='')
{
    //template bootstrap tanpa row and column
//	start_row();
//		start_column("width='10%'");
			label($label);
//		end_column();
//		start_column();
			input_amount_text($name, $value, $required, $params);
//		end_column();
//	end_row();
}


/**
 * text label one row (for form input with input hidden type)
 * @param string $label label text tulisan
 * @param string $name name hidden form
 * @param string $id_value value hidden
 * @param string $value nilai dari label (akan muncul disamping label)
 * @param string $params parameter tambahan di hidden form
 */
function text_label_with_hidden($label='', $name='', $id_value='', $value=null, $params='')
{
    //template bootstrap tanpa row and column
//	start_row();
//		start_column("width='10%'");
			label($label);
//		end_column();
//		start_column();
                    space(2);
                    echo $value;
                    input_hidden($name, $id_value, $params);
//		end_column();
//	end_row();
}

/**
* text table one cell (for table display)
**/
function text_cell($text='', $params='')
{
	start_column($params);
	echo $text;
	end_column();
}

/**
* space kolom table one cell (for table display)
**/
function space_cell($params='')
{
	start_column($params);
	echo "&nbsp;";
	end_column();
}

/**
* head table parameter harus diisi array
 * 
**/
function head_table($head, $thead=false){
    if($thead){
        echo "<thead>";
    }
	start_row();
	foreach ($head as $val => $value) {
		echo "<th>".$value."</th>";
	}
	end_row();
    if($thead){
        echo "</thead>";
    }
}

/**
 * foot table parameter harus diisi array
 * @param type $head
 * @param type $thead
 */
function foot_table($head, $thead=false){
    if($thead){
        echo "<tfoot>";
    }
	start_row();
	foreach ($head as $val => $value) {
		echo "<th>".$value."</th>";
	}
	end_row();
    if($thead){
        echo "</tfoot>";
    }
}

/**
 * edit dan delete link button(for table display)
 * @param type $table
 * @param type $id
 * @param type $parameter_key
 * @param type $path_to_root
 * @param type $parent_menu headmenu parameter get
 */
function edit_delete_link($table, $id, $parameter_key, $path_to_root, $parent_menu){
	start_column("align='center' ");
        edit_link($table, $id, $parameter_key, $path_to_root, $parent_menu);
//	echo " | ";
        delete_link($table, $id, $parameter_key, $path_to_root, $parent_menu);
	end_column();
}

/**
 * edit link
 * @param type $table
 * @param type $id
 * @param type $parameter_key
 * @param type $path_to_root
 * @param type $parent_menu headmenu parameter get
 */
function edit_link($table, $id, $parameter_key, $path_to_root, $parent_menu){
    echo "<a href='?".$parameter_key."kd_tabel=$table&headmenu=$parent_menu&update=$id' class='btn bg-olive btn-sm margin' data-toggle='tooltip' title='Edit' >"
            . "<i class='glyphicon glyphicon-edit' ></i>"
            . "</a>";
}

/**
 * delete link
 * @param type $table
 * @param type $id
 * @param type $parameter_key
 * @param type $path_to_root
 * @param type $parent_menu headmenu parameter get
 * @param type $params tambahan parameter get
 */
function delete_link($table, $id, $parameter_key, $path_to_root, $parent_menu, $params=''){
    $prm='';
    if(!empty($params)){
        $prm = "&".$params;
    }
    echo "<a href='?".$parameter_key."kd_tabel=$table&headmenu=$parent_menu&delete=".$id.$prm."' onclick=\"return confirm('Apakah anda yakin?')\" class='btn btn-danger btn-sm' data-toggle='tooltip' title='Delete'>"
            . "<i class='glyphicon glyphicon-remove-sign' ></i>"
            . "</a> ";
}

/**
 * kolom link text
 * @param type $link
 * @param type $label
 * @param type $params
 */
function colom_link_text($link, $label, $params=''){
        start_column("align='center' ");
        link_text($link, $label, $params);
	end_column();
}

/**
 * input type password
 * @param type $name
 * @param type $value
 * @param type $required
 * @param type $params
 */
function input_password_text($name,$value, $required=true, $params=''){
	
	if(!$required){
		echo "<input type='password'  name='$name' 
			value='$value' class='form-control' $params/>";
	}
	else{
		echo "<input type='password' name='$name' 
			value='$value' required='required' class='form-control' $params/>";
	}
}

/**
 * input type text
 * @param type $name
 * @param type $value
 * @param type $required
 * @param type $params
 */
function input_free_text($name,$value, $required=true, $params=''){
	
	if(!$required){
		echo "<input type='text'  name='$name' 
			value='$value' class='form-control' $params/>";
	}
	else{
		echo "<input type='text' name='$name' 
			value='$value' required='required' class='form-control' $params/>";
	}
}

/**
 * input tipe file
 * @param type $name
 * @param type $required
 * @param type $params
 */
function input_file($name, $required=false, $params=''){
    if(!$required){
        echo "<input type='file' name='$name' $params>";
    }
    else{
        echo "<input type='file' name='$name' required='required' $params>";
    }
}

/**
 * input type amount (harga)
 * @param type $name
 * @param type $value
 * @param type $required
 * @param type $params
 */
function input_amount_text($name,$value, $required=true, $params=''){
	
        $value = price_format($value);
	if(!$required){
		echo "<input type='text'  name='$name' 
			value='$value' onkeyup=\"convertToPrice(this);\" class='form-control' $params/>";
	}
	else{
		echo "<input type='text' name='$name' 
			value='$value' onkeyup=\"convertToPrice(this);\" required='required' class='form-control' $params/>";
	}
}

/**
 * input type hidden
 * @param type $name
 * @param type $value
 * @param type $params default = ''
 */
function input_hidden($name, $value, $params=''){
	echo "<input type='hidden' id='$name' name='$name' value='$value' $params/>";
}

/**
 * input type date
 * @param type $name
 * @param type $value
 * @param type $tittle
 * @param type $id
 */
function input_date($name, $value, $tittle='', $id='date-picker'){
	echo "<input type='text' id='$id'  name='$name' size='10' maxlength='10' 
			value='$value' tittle ='$tittle' />";	
}

/**
 * submit button with reset button
 * @param type $name
 * @param type $value
 */
function submit_form_button($name, $value){
	echo "<input type='submit' name='$name' value='$value' >";
	echo "<input type='reset' value='Reset'>";
}

/**
 * submit button 
 * @param type $name
 * @param type $value
 * @param type $params default=''
 */
function submit_button($name, $value, $params=''){
	echo "<button name='$name' value='$value' $params >$value</button>";
	// echo "<input type='submit' name='$name' value='$value' >";
}

/**
 * link dengan popup new window open
 * @param type $label
 * @param type $link
 */
function link_new_window($label, $link){
	echo  "<a href='$link' target='_blank' onclick=\"window.open(this.href); return false;\" 
	 onkeypress=\"window.open(this.href); return false;\">$label</a>";
}

/**
 * tulisan untuk link ke suatu halaman
 * @param type $link
 * @param type $label
 * @param type $params
 */
function link_text($link, $label, $params='')
{
	echo "<a href='$link' $params />$label</a>";
}

/**
 * format number 2 dibelakang koma (number_format($value,2)
 * @param type $value
 * @return type
 */
function price_format($value){
	return number_format($value,2, ',', '.');
}

/**
 * cetak link
 */
function print_cetak(){
    echo "<a href=\"javascript:window.print()\">Cetak</a>";
}

function format_date($date){
    $date_ex = explode("/", $date);
    return $date_ex[2]."-".$date_ex[1]."-".$date_ex[0];
}

function format_date2($date){
    $date_ex = explode("-", $date);
    return $date_ex[2]."/".$date_ex[1]."/".$date_ex[0];
}

function format_date_db($date){
    $date_ex = explode("-", $date);
    return $date_ex[2]."-".$date_ex[1]."-".$date_ex[0];
}

?>