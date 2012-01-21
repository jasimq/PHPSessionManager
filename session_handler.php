class SessionManager {

    var $session_life_time;

    function SessionManager() 
    {
        $this->session_life_time = get_cfg_var("session.gc_maxlifetime");

        session_set_save_handler( 
          array( &$this, "open" ), 
          array( &$this, "close" ),
          array( &$this, "read" ),
          array( &$this, "write"),
          array( &$this, "destroy"),
          array( &$this, "gc" )
        );
    }
    
    function open() 
    {   
        return true;
    }

    function close() 
    {
        return true;
    }
    
    function read($id) 
    {
        global $conn;
    
        $data = '';
        $time = time();
        
        $sql = "SELECT data FROM user_sessions WHERE id = '$id' AND expires > $time";
    
        $res = mysql_query($sql, $conn) or die(mysql_error());
        if(mysql_num_rows($res) > 0) {
              $row = mysql_fetch_array($res);
              $data = $row['data'];
        }
        return $data;
    }
    
    function write($id, $data) 
    {
        global $conn;
    
        $time = time() + $this->session_life_time;
    
        $sql = "REPLACE user_sessions (id, data, expires) VALUES('$id', '$data', $time)";
        mysql_query($sql, $conn) or die(mysql_error());
    
        return true;
    }
    
    function destroy($id) 
    {
        global $conn;
          
        $sql = "DELETE FROM user_sessions  WHERE id ='$id'";
        mysql_query($sql, $conn) or die(mysql_error()); 
    
        return true;
    }

    function gc() 
    { 
        global $conn;
 
        $sql = "DELETE FROM user_sessions  WHERE expires < UNIX_TIMESTAMP();";
        mysql_query($sql, $conn) or die(mysql_error());

        return true;
    }
}

