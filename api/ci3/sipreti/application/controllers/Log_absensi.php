<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Log_absensi extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Log_absensi_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$q = urldecode($this->input->get('q', TRUE));
		$start = intval($this->input->get('start'));

		if ($q <> '') {
			$config['base_url'] = base_url() . 'log_absensi/index.html?q=' . urlencode($q);
			$config['first_url'] = base_url() . 'log_absensi/index.html?q=' . urlencode($q);
		} else {
			$config['base_url'] = base_url() . 'log_absensi/index.html';
			$config['first_url'] = base_url() . 'log_absensi/index.html';
		}

		$config['per_page'] = 10;
		$config['page_query_string'] = TRUE;
		$config['total_rows'] = $this->Log_absensi_model->total_rows($q, TRUE);
		$log_absensi = $this->Log_absensi_model->get_limit_data($config['per_page'], $start, $q, TRUE);

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$data = array(
			'log_absensi_data' => $log_absensi,
			'q' => $q,
			'pagination' => $this->pagination->create_links(),
			'total_rows' => $config['total_rows'],
			'start' => $start,
		);
		$this->load->view('log_absensi/log_absensi_list', $data);
	}

	public function read($id)
	{
		$row = $this->Log_absensi_model->get_by_id($id);
		if ($row && empty($row->deleted_at)) {
			$data = array(
				'id_log_absensi' => $row->id_log_absensi,
				'id_pegawai' => $row->id_pegawai,
				'jenis_absensi' => $row->jenis_absensi,
				'check_mode' => $row->check_mode,
				'waktu_absensi' => $row->waktu_absensi,
				'lattitude' => $row->lattitude,
				'longitude' => $row->longitude,
				'nama_lokasi' => $row->nama_lokasi,
				'nama_kamera' => $row->nama_kamera,
				'url_foto_presensi' => $row->url_foto_presensi,
				'url_dokumen' => $row->url_dokumen,
				'created_at' => $row->created_at,
				'updated_at' => $row->updated_at,
				'deleted_at' => $row->deleted_at,
			);
			$this->load->view('log_absensi/log_absensi_read', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('log_absensi'));
		}
	}

	public function create()
	{
		$data = array(
			'button' => 'Create',
			'action' => site_url('log_absensi/create_action'),
			'id_log_absensi' => set_value('id_log_absensi'),
			'id_pegawai' => set_value('id_pegawai'),
			'jenis_absensi' => set_value('jenis_absensi'),
			'check_mode' => set_value('check_mode'),
			'waktu_absensi' => set_value('waktu_absensi'),
			'lattitude' => set_value('lattitude'),
			'longitude' => set_value('longitude'),
			'nama_lokasi' => set_value('nama_lokasi'),
			'nama_kamera' => set_value('nama_kamera'),
			'url_foto_presensi' => set_value('url_foto_presensi'),
			'url_dokumen' => set_value('url_dokumen'),
		);
		$this->load->view('log_absensi/log_absensi_form', $data);
	}

	public function create_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->create();
		} else {
			$data = array(
				'id_pegawai' => $this->input->post('id_pegawai', TRUE),
				'jenis_absensi' => $this->input->post('jenis_absensi', TRUE),
				'check_mode' => $this->input->post('check_mode', TRUE),
				'waktu_absensi' => $this->input->post('waktu_absensi', TRUE),
				'lattitude' => $this->input->post('lattitude', TRUE),
				'longitude' => $this->input->post('longitude', TRUE),
				'nama_lokasi' => $this->input->post('nama_lokasi', TRUE),
				'nama_kamera' => $this->input->post('nama_kamera', TRUE),
				'url_foto_presensi' => $this->input->post('url_foto_presensi', TRUE),
				'url_dokumen' => $this->input->post('url_dokumen', TRUE),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => NULL,
				'deleted_at' => NULL,
			);

			$this->Log_absensi_model->insert($data);
			$this->session->set_flashdata('message', 'Create Record Success');
			redirect(site_url('log_absensi'));
		}
	}

	public function update($id)
	{
		$row = $this->Log_absensi_model->get_by_id($id);

		if ($row) {
			$data = array(
				'button' => 'Update',
				'action' => site_url('log_absensi/update_action'),
				'id_log_absensi' => set_value('id_log_absensi', $row->id_log_absensi),
				'id_pegawai' => set_value('id_pegawai', $row->id_pegawai),
				'jenis_absensi' => set_value('jenis_absensi', $row->jenis_absensi),
				'check_mode' => set_value('check_mode', $row->check_mode),
				'waktu_absensi' => set_value('waktu_absensi', $row->waktu_absensi),
				'lattitude' => set_value('lattitude', $row->lattitude),
				'longitude' => set_value('longitude', $row->longitude),
				'nama_lokasi' => set_value('nama_lokasi', $row->nama_lokasi),
				'nama_kamera' => set_value('nama_kamera', $row->nama_kamera),
				'url_foto_presensi' => set_value('url_foto_presensi', $row->url_foto_presensi),
				'url_dokumen' => set_value('url_dokumen', $row->url_dokumen),
			);
			$this->load->view('log_absensi/log_absensi_form', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('log_absensi'));
		}
	}

	public function update_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->update($this->input->post('id_log_absensi', TRUE));
		} else {
			$data = array(
				'id_pegawai' => $this->input->post('id_pegawai', TRUE),
				'jenis_absensi' => $this->input->post('jenis_absensi', TRUE),
				'check_mode' => $this->input->post('check_mode', TRUE),
				'waktu_absensi' => $this->input->post('waktu_absensi', TRUE),
				'lattitude' => $this->input->post('lattitude', TRUE),
				'longitude' => $this->input->post('longitude', TRUE),
				'nama_lokasi' => $this->input->post('nama_lokasi', TRUE),
				'nama_kamera' => $this->input->post('nama_kamera', TRUE),
				'url_foto_presensi' => $this->input->post('url_foto_presensi', TRUE),
				'url_dokumen' => $this->input->post('url_dokumen', TRUE),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			$this->Log_absensi_model->update($this->input->post('id_log_absensi', TRUE), $data);
			$this->session->set_flashdata('message', 'Update Record Success');
			redirect(site_url('log_absensi'));
		}
	}

	public function delete($id)
	{
		$row = $this->Log_absensi_model->get_by_id($id);

		if ($row) {
			$data = array(
				'deleted_at' => date('Y-m-d H:i:s'),
			);

			$this->Log_absensi_model->update($id, $data);
			$this->session->set_flashdata('message', 'Delete Record Success');
			redirect(site_url('log_absensi'));
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('log_absensi'));
		}
	}

	public function _rules()
	{
		$this->form_validation->set_rules('id_pegawai', 'id pegawai', 'trim|required');
		$this->form_validation->set_rules('jenis_absensi', 'jenis absensi', 'trim|required');
		$this->form_validation->set_rules('check_mode', 'check mode', 'trim|required');
		$this->form_validation->set_rules('waktu_absensi', 'waktu absensi', 'trim|required');
		$this->form_validation->set_rules('lattitude', 'lattitude', 'trim|required|numeric');
		$this->form_validation->set_rules('longitude', 'longitude', 'trim|required|numeric');
		$this->form_validation->set_rules('nama_lokasi', 'nama lokasi', 'trim|required');
		$this->form_validation->set_rules('nama_kamera', 'nama kamera', 'trim|required');
		$this->form_validation->set_rules('url_foto_presensi', 'url foto presensi', 'trim|required');
		$this->form_validation->set_rules('url_dokumen', 'url dokumen', 'trim|required');

		$this->form_validation->set_rules('id_log_absensi', 'id_log_absensi', 'trim');
		$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
	}

}

/* End of file Log_absensi.php */
/* Location: ./application/controllers/Log_absensi.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2025-03-12 08:14:34 */
/* http://harviacode.com */
