<?php

/*
 * Class Configuration Database
 */

class database {

    private $servername;
    private $user_db;
    private $password_db;
    private $database;
    private $koneksi;

    /**
     * fungsi construktor awal dan koneksi ke database;
     * set variable class $this->conn;
     */
    function database() {
        $this->load_conf_db();
        return $this->connect_db();
    }

    function load_conf_db() {
        $path = dirname(__FILE__) . '/koneksi.php';
        if (file_exists($path)) {
            $conf = include $path;
            $this->servername = @$conf['host'];
            $this->database = @$conf['dbname'];
            $this->user_db = @$conf['username'];
            $this->password_db = @$conf['password'];
        }
    }

    /**
     * connection database
     * @return type
     */
    public function connect_db() {
        $this->koneksi = mysqli_connect($this->servername, $this->user_db, $this->password_db, $this->database);
        return $this->koneksi;
    }

    /**
     * execute query mysql_query
     * @param string $sql
     * @return type
     */
    function db_query($sql) {
        $conn = $this->koneksi;
        return mysqli_query($conn, $sql);
    }

    /**
     * mysql_error
     * @return type
     */
    function db_error($result) {
        $conn = $this->koneksi;
        return mysqli_error($conn);
    }

    /**
     * mysql_fetch_array
     * @param type $result
     * @return type
     */
    function db_fetch_array($result) {
        return mysqli_fetch_array($result);
    }

    /**
     * mysql_num_rows
     * @param type $result
     * @return type
     */
    function db_num_rows($result) {
        return mysqli_num_rows($result);
    }

    /**
     * mysql_insert_id
     * @return type
     */
    function db_insert_id() {
        $conn = $this->koneksi;
        return mysqli_insert_id($conn);
    }
    


//==============================================================================
    /**
     * Insert Data within table by accepting TableName and Table column => Data as associative array
     * contoh $val_cols = array("Name"=>'ido',"Age"=>23);
     * @param string $table
     * @param array $val_cols 
     * @return type hasil query (db_query)
     */
    function insert_record($table, array $val_cols) {
        $field = implode("`, `", array_keys($val_cols));
        $i = 0;
        foreach ($val_cols as $key => $value) {
            $StValue[$i] = "'" . $value . "'";
            $i++;
        }
        $StValues = implode(", ", $StValue);

        $sql = "INSERT INTO $table (`$field`) VALUES ($StValues)";
        
        $result = $this->db_query($sql);
        return $result;
    }

    /**
     * Delete data form table; Accepting Table Name and Keys=>Values as associative array
     * contoh $val_cols = array("id"=>4,"Name"=>'ido');
     * @param string $table
     * @param array $val_cols where condition implode by AND
     * @return type hasil query (db_query)
     */
    function delete_record($table, array $val_cols) {
        //Append each element of val_cols associative array 
        $i = 0;
        foreach ($val_cols as $key => $value) {
            $exp[$i] = $key . " = '" . $value . "'";
            $i++;
        }
        $Stexp = implode(" AND ", $exp);

        $sql = "DELETE FROM $table WHERE $Stexp ";

        return $this->db_query($sql);
    }

    /**
     * Update data within table; Accepting Table Name and Keys=>Values as associative array
     * contoh $set_val_cols = array("Name"=>'Ido',"Nilai"=>98.9);
     * contoh $cod_val_cols = array("id"=>'23');
     * @param type $table
     * @param array $set_val_cols set update
     * @param array $cod_val_cols where condition implode by AND
     * @return type
     */
    function update_record($table, array $set_val_cols, array $cod_val_cols) {
        //append set_val_cols associative array elements 
        $i = 0;
        foreach ($set_val_cols as $key => $value) {
            $set[$i] = $key . " = '" . $value . "'";
            $i++;
        }
        $Stset = implode(", ", $set);

        //append cod_val_cols associative array elements
        $i = 0;
        foreach ($cod_val_cols as $key => $value) {
            $cod[$i] = $key . " = '" . $value . "'";
            $i++;
        }
        $Stcod = implode(" AND ", $cod);

        $sql = "UPDATE $table SET $Stset WHERE $Stcod";

        return $this->db_query($sql);
    }

    //--------------------------------------------------------------------------

    /**
     * menghitung jumlah data (count)
     * @param type $table
     * @param type $field
     * @param type $where default=null, diisi dengan kolom dan isi nya contoh warna='blue'
     * @return int hasil count (db_fetch_array)
     */
    function count_data($table, $field, $where = null) {
        $sql = "SELECT COUNT($field) FROM $table ";

        if ($where != null || $where != '') {
            $sql .= " WHERE $where ";
        }

        $result = $this->db_query($sql);
        return $row = $this->db_fetch_array($result);
    }

    /**
     * ambil data table semua kolom return bisa db_query/db_fetch_array
     * bisa di limit
     * @param type $table
     * @param type $where default=null
     * @param type $fetch default=false
     * @param type $limit default=false
     * @param type $limit_posisi default=0
     * @param type $limit_batas default=0
     * @param type $sort default='', order by...
     * @return fetch/db_query
     */
    function display_table_all_column($table, $where = null, $fetch = false, $limit = false, $limit_posisi = 0, $limit_batas = 0, $sort = '') {
        $sql = "SELECT * FROM $table ";

        if ($where != null || $where != '') {
            $sql .= " WHERE $where";
        }

        if (!empty($sort)) {
            $sql .= " ORDER BY " . $sort;
        }

        if ($limit) {
            $sql .= " LIMIT $limit_posisi , $limit_batas ";
        }

        if ($fetch) {
            $result = $this->db_query($sql);
            return $row = $this->db_fetch_array($result);
        } else {
            return $result = $this->db_query($sql);
        }
    }

    /**
     * mencari sesuatu dalam sebuah table
     * 
     * @param string $table 
     * @param array string $find_column default = array()
     * @param string $where default=''
     * @return array db_fetch_array
     */
    function find_in_table($table, $find_column = array(), $where = '') {
        $sql = "SELECT ";
        $column = '';
        if (!is_array($find_column)) {
            $column = $find_column;
        } else {
            $column = implode(",", $find_column);
        }
        $sql .= $column . " FROM " . $table . " " . $where;

        $result = $this->db_query($sql);
        $rows = $this->db_fetch_array($result);

        return $rows;
    }

    /**
     * cek value data pada suatu table
     * @param string $table
     * @param string $field
     * @param string $value
     * @return boolean true jika ada , false jika tidak ada
     * */
    function cek_data_is_in_table($table, $field, $value) {
        $sql = "SELECT COUNT(" . $field . ") FROM " . $table . " WHERE " . $field . " = '" . $value . "'";
        $result = $this->db_query($sql);
        $num = $this->db_fetch_array($result);

        if ($num[0] > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ambil data login user
     * @param type $id_login
     * @return type
     */
    function get_login_by_id($id_login){
        $sql = "SELECT * FROM login WHERE id_login = ".$id_login;
        $result = $this->db_query($sql);
        $row = $this->db_fetch_array($result);
        return $row;
    }

}

?>
