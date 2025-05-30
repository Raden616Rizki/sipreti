<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Log_absensi_model extends CI_Model
{

	public $table = 'log_absensi';
	public $id = 'id_log_absensi';
	public $order = 'DESC';

	function __construct()
	{
		parent::__construct();
	}

	// get all
	function get_all()
	{
		$this->db->order_by($this->id, $this->order);
		return $this->db->get($this->table)->result();
	}

	// get data by id
	function get_by_id($id)
	{
		$this->db->where($this->id, $id);
		return $this->db->get($this->table)->row();
	}

	// get total rows
	function total_rows($q = NULL, $onlyActive = FALSE)
	{
		$this->db->like('id_pegawai', $q);
		if ($onlyActive) {
			$this->db->where('(deleted_at IS NULL OR deleted_at = "")');
		}
		return $this->db->count_all_results($this->table);
	}

	// get data with limit and search
	public function get_limit_data($limit, $start = 0, $q = NULL, $onlyActive = FALSE)
	{
		$this->db->select('log_absensi.*, pegawai.nama');
		$this->db->from($this->table);
		$this->db->join('pegawai', 'log_absensi.id_pegawai = pegawai.id_pegawai', 'left');

		if (!empty($q)) {
			$this->db->like('log_absensi.id_pegawai', $q);
			$this->db->or_like('pegawai.nama', $q);
		}

		if ($onlyActive) {
			$this->db->where('(log_absensi.deleted_at IS NULL OR log_absensi.deleted_at = "")');
		}

		$this->db->order_by('log_absensi.waktu_absensi', 'DESC');

		$this->db->limit($limit, $start);
		return $this->db->get()->result();
	}

	// insert data
	function insert($data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	// update data
	function update($id, $data)
	{
		$this->db->where($this->id, $id);
		$this->db->update($this->table, $data);
	}

	// delete data
	function delete($id)
	{
		$this->db->where($this->id, $id);
		$this->db->delete($this->table);
	}

	// get all data by id_pegawai
	public function get_rekap_absensi($id_pegawai)
	{
		$this->db->select("
			DATE(waktu_absensi) AS tanggal,
			DAYNAME(waktu_absensi) AS hari,
			MIN(CASE WHEN check_mode = 0 THEN waktu_absensi END) AS jam_datang,
			MIN(CASE WHEN check_mode = 1 THEN waktu_absensi END) AS jam_pulang,
    	");
		$this->db->from($this->table);
		$this->db->where('id_pegawai', $id_pegawai);
		$this->db->where('deleted_at IS NULL');
		$this->db->group_by('DATE(waktu_absensi)');
		$this->db->order_by('tanggal', 'DESC');

		return $this->db->get()->result();
	}

}

/* End of file Log_absensi_model.php */
/* Location: ./application/models/Log_absensi_model.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2025-03-12 08:14:34 */
/* http://harviacode.com */
