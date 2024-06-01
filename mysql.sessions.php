<?php

	/*
	Revised code by Dominick Lee
	Original code derived from "Essential PHP Security" by Chriss Shiflett
	Last Modified 2/27/2017


	CREATE TABLE sessions
	(
		id varchar(32) NOT NULL,
		access int(10) unsigned,
		data text,
		PRIMARY KEY (id)
	);

	+--------+------------------+------+-----+---------+-------+
	| Field  | Type             | Null | Key | Default | Extra |
	+--------+------------------+------+-----+---------+-------+
	| id     | varchar(32)      |      | PRI |         |       |
	| access | int(10) unsigned | YES  |     | NULL    |       |
	| data   | text             | YES  |     | NULL    |       |
	+--------+------------------+------+-----+---------+-------+

	*/


class Session {
	private $db;

	public function __construct(){
		// Instantiate new Database object
		$this->db = new Database;

		// Set handler to overide SESSION
		session_set_save_handler(
			array($this, "_open"),
			array($this, "_close"),
			array($this, "_read"),
			array($this, "_write"),
			array($this, "_destroy"),
			array($this, "_gc")
		);

		// Start the session
		session_start();
	}
	public function _open(){
		return $this->db;
		
	}
	public function _close(){
		// Close the database connection
		return $this->db->close()
	}
	public function _read($id){
		// Set query
		$this->db->query('SELECT data FROM sessions WHERE id = :id');
		// Bind the Id
		$this->db->bind(':id', $id);
		// Attempt execution

		// return empty string since execution failed.
		if(!$this->db->execute())
		{
			return '';
		}
		
		if($this->db->rowCount() > 0)
		{
			// Save returned row
			$row = $this->db->single();
			// Return the data
			return $row['data'];
		}
	}
	public function _write($id, $data){
		/
		// Set query  
		$this->db->query('REPLACE INTO sessions VALUES (:id, NOW(), :data)');
		// Bind data
		$this->db->bind(':id', $id);
		$this->db->bind(':data', $data);
		// Attempt Execution
		return $this->db->execute();
	}
	public function _destroy($id){
		// Set query
		$this->db->query('DELETE FROM sessions WHERE id = :id');
		// Bind data
		$this->db->bind(':id', $id);
		// Attempt execution
		// If successful
		return $this->db->execute();
	} 
	public function _gc($max){
		// Calculate what is to be deemed old
		$old = time() - $max;
		// Set query
		$this->db->query('DELETE FROM sessions WHERE access < :old');
		// Bind data
		$this->db->bind(':old', $old);
		// Attempt execution
		return $this->db->execute();
	}
}
?>
