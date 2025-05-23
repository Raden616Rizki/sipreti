<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Pegawai_model extends CI_Model
{

	public $table = 'pegawai';
	public $id = 'id_pegawai';
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
	public function get_by_id($id)
	{
		$this->db->select('pegawai.*, jabatan.nama_jabatan, unit_kerja.nama_unit_kerja');
		$this->db->from($this->table);
		$this->db->join('jabatan', 'jabatan.id_jabatan = pegawai.id_jabatan', 'left');
		$this->db->join('unit_kerja', 'unit_kerja.id_unit_kerja = pegawai.id_unit_kerja', 'left');
		$this->db->where('pegawai.' . $this->id, $id);
		return $this->db->get()->row();
	}

	// get data by id api
	public function get_by_id_api($id)
	{
		$this->db->select('
        pegawai.*, 
        jabatan.nama_jabatan,
        unit_kerja.nama_unit_kerja, 
        unit_kerja.alamat, 
        unit_kerja.lattitude, 
        unit_kerja.longitude,
        radius_absen.ukuran,
    ');
		$this->db->from('pegawai');
		$this->db->join('jabatan', 'jabatan.id_jabatan = pegawai.id_jabatan', 'left');
		$this->db->join('unit_kerja', 'unit_kerja.id_unit_kerja = pegawai.id_unit_kerja', 'left');
		$this->db->join('radius_absen', 'radius_absen.id_radius = unit_kerja.id_radius', 'left');
		$this->db->where('pegawai.id_pegawai', $id);
		$this->db->where('pegawai.deleted_at IS NULL');
		$pegawai = $this->db->get()->row_array();

		if ($pegawai) {
			$this->db->select('face_embeddings');
			$this->db->from('vektor_pegawai');
			$this->db->where('id_pegawai', $id);
			$this->db->where('deleted_at IS NULL');
			$query = $this->db->get();
			$embeddings = [];

			foreach ($query->result_array() as $row) {
				$decoded = json_decode($row['face_embeddings'], true);
				if (is_array($decoded)) {
					$embeddings[] = $decoded;
				}
			}

			$pegawai['face_embeddings'] = $embeddings;

			return $pegawai;
		}

		return null;
	}


	// get total rows
	function total_rows($q = NULL, $onlyActive = FALSE)
	{
		$this->db->like('nama', $q);
		if ($onlyActive) {
			$this->db->where('(deleted_at IS NULL OR deleted_at = "")');
		}
		return $this->db->count_all_results($this->table);
	}

	// get data with limit and search
	public function get_limit_data($limit, $start = 0, $q = NULL, $onlyActive = FALSE)
	{
		$this->db->select('
        pegawai.*, 
        jabatan.nama_jabatan, 
        unit_kerja.nama_unit_kerja, 
        COUNT(CASE 
            WHEN vektor_pegawai.id_vektor_pegawai IS NOT NULL 
                 AND (vektor_pegawai.deleted_at IS NULL OR vektor_pegawai.deleted_at = "") 
            THEN 1 
            ELSE NULL 
        END) AS jumlah_biometrik
    ');
		$this->db->from($this->table);

		$this->db->join('jabatan', 'pegawai.id_jabatan = jabatan.id_jabatan', 'left');
		$this->db->join('unit_kerja', 'pegawai.id_unit_kerja = unit_kerja.id_unit_kerja', 'left');
		$this->db->join('vektor_pegawai', 'pegawai.id_pegawai = vektor_pegawai.id_pegawai', 'left');

		if (!empty($q)) {
			$this->db->like('pegawai.nama', $q);
		}

		if ($onlyActive) {
			$this->db->where('(pegawai.deleted_at IS NULL OR pegawai.deleted_at = "")');
		}

		$this->db->group_by('pegawai.id_pegawai');
		$this->db->order_by('pegawai.nama', 'ASC');
		$this->db->limit($limit, $start);

		return $this->db->get()->result();
	}


	// insert data
	function insert($data)
	{
		$this->db->insert($this->table, $data);
	}

	public function insert_api($data)
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

	public function update_relation($id_pegawai_lama, $id_pegawai_baru)
	{
		$this->db->where('id_pegawai', $id_pegawai_lama);
		$this->db->update('user_android', ['id_pegawai' => $id_pegawai_baru]);

		$this->db->where('id_pegawai', $id_pegawai_lama);
		$this->db->update('log_absensi', ['id_pegawai' => $id_pegawai_baru]);
	}


	// delete data
	function delete($id)
	{
		$this->db->where($this->id, $id);
		$this->db->delete($this->table);
	}

	public function get_by_nip($nip)
	{
		$this->db->where('nip', $nip);
		$this->db->where('deleted_at IS NULL');
		$query = $this->db->get('pegawai');

		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return null;
		}
	}

	public function count_by_nip($nip)
	{
		$this->db->where('nip', $nip);
		$query = $this->db->get('pegawai');

		return $query->num_rows() > 0 ? $query->row() : null;
	}

	public function delete_by_nip($nip)
	{
		$pegawai = $this->db->get_where('pegawai', ['nip' => $nip])->row();

		if ($pegawai) {
			$id_pegawai = $pegawai->id_pegawai;

			$this->db->where('id_pegawai', $id_pegawai);
			$this->db->delete('vektor_pegawai');

			$this->db->where('nip', $nip);
			$this->db->delete('pegawai');
		}
	}



	public function get_all_for_csv_export()
	{
		$this->db->select('
			p.id_pegawai,
			p.nama,
			p.nip,
			u.nama_unit_kerja,
			"" AS url_photo_folder
		');
		$this->db->from('pegawai p');
		$this->db->join('unit_kerja u', 'p.id_unit_kerja = u.id_unit_kerja', 'left');
		$this->db->where('(p.deleted_at IS NULL OR p.deleted_at = "")', null, false);
		$this->db->order_by('p.nama', 'ASC');

		return $this->db->get();
	}

}

/* End of file Pegawai_model.php */
/* Location: ./application/models/Pegawai_model.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2025-03-12 07:46:51 */
/* http://harviacode.com */
