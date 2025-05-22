<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Log_absensi extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Log_absensi_model');
		$this->load->model('Pegawai_model');
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
				'url_foto_presensi' => $row->url_foto_presensi,
				'url_dokumen' => $row->url_dokumen,
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

	public function create_api()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$response = array(
				'status' => 400,
				'message' => validation_errors()
			);
		} else {
			$id_pegawai = $this->input->post('id_pegawai', TRUE);
			$timestamp = date('Ymd_His');

			// Direktori penyimpanan berdasarkan id_pegawai
			$upload_path = './uploads/presensi/' . $id_pegawai . '/';
			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0777, true);
			}

			// Konfigurasi upload file
			$config['upload_path'] = $upload_path;
			$config['allowed_types'] = 'jpg|jpeg|png|pdf|doc|docx';
			$config['max_size'] = 2048; // Maksimal 5MB
			$this->load->library('upload', $config);

			$url_foto_presensi = NULL;
			$url_dokumen = NULL;
			$errors = [];

			// Upload foto presensi jika ada
			if (!empty($_FILES['url_foto_presensi']['name'])) {
				$foto_ext = pathinfo($_FILES['url_foto_presensi']['name'], PATHINFO_EXTENSION);
				$foto_filename = 'foto_' . $timestamp . '.' . $foto_ext;
				$config['file_name'] = $foto_filename;
				$this->upload->initialize($config);

				if ($this->upload->do_upload('url_foto_presensi')) {
					$url_foto_presensi = $foto_filename;
				} else {
					$errors[] = "Foto presensi: " . $this->upload->display_errors('', '');
				}
			}

			// Upload dokumen jika ada
			if (!empty($_FILES['url_dokumen']['name'])) {
				$doc_ext = pathinfo($_FILES['url_dokumen']['name'], PATHINFO_EXTENSION);
				$doc_filename = 'dokumen_' . $timestamp . '.' . $doc_ext;
				$config['file_name'] = $doc_filename;
				$this->upload->initialize($config);

				if ($this->upload->do_upload('url_dokumen')) {
					$url_dokumen = $doc_filename;
				} else {
					$errors[] = "Dokumen: " . $this->upload->display_errors('', '');
				}
			}

			// Jika ada error saat upload, kembalikan response error
			if (!empty($errors)) {
				$response = array(
					'status' => 400,
					'message' => implode("; ", $errors)
				);
			} else {
				$data = array(
					'id_pegawai' => $id_pegawai,
					'jenis_absensi' => $this->input->post('jenis_absensi', TRUE),
					'check_mode' => $this->input->post('check_mode', TRUE),
					'waktu_absensi' => $this->input->post('waktu_absensi', TRUE),
					'lattitude' => $this->input->post('lattitude', TRUE),
					'longitude' => $this->input->post('longitude', TRUE),
					'nama_lokasi' => $this->input->post('nama_lokasi', TRUE),
					'waktu_verifikasi' => $this->input->post('waktu_verifikasi', TRUE),
					'jarak_vektor' => $this->input->post('jarak_vektor', TRUE),
					'url_foto_presensi' => $url_foto_presensi,
					'url_dokumen' => $url_dokumen,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => NULL,
					'deleted_at' => NULL,
				);

				// Simpan data ke database
				$this->Log_absensi_model->insert($data);

				$response = array(
					'status' => 200,
					'message' => 'Absensi berhasil disimpan',
					'data' => $data
				);
			}
		}

		// Output response JSON
		$this->output
			->set_content_type('application/json')
			->set_status_header($response['status'])
			->set_output(json_encode($response));
	}

	public function list_pegawai()
	{
		$q = urldecode($this->input->get('q', TRUE));
		$start = intval($this->input->get('start'));

		if ($q <> '') {
			$config['base_url'] = base_url() . 'log_absensi/list_pegawai.html?q=' . urlencode($q);
			$config['first_url'] = base_url() . 'log_absensi/list_pegawai.html?q=' . urlencode($q);
		} else {
			$config['base_url'] = base_url() . 'log_absensi/list_pegawai.html';
			$config['first_url'] = base_url() . 'log_absensi/list_pegawai.html';
		}

		$config['per_page'] = 10;
		$config['page_query_string'] = TRUE;
		$config['total_rows'] = $this->Pegawai_model->total_rows($q, TRUE);
		$pegawai = $this->Pegawai_model->get_limit_data($config['per_page'], $start, $q, TRUE);

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$data = array(
			'pegawai_data' => $pegawai,
			'q' => $q,
			'pagination' => $this->pagination->create_links(),
			'total_rows' => $config['total_rows'],
			'start' => $start,
		);
		$this->load->view('log_absensi/log_absensi_pegawai', $data);
	}

	public function read_absensi_pegawai($id)
	{
		if ($id === null) {
			show_error("ID Pegawai tidak ditemukan.", 400);
			return;
		}

		$pegawai = $this->Pegawai_model->get_by_id($id);
		if (!$pegawai) {
			show_error("Pegawai tidak ditemukan.", 404);
			return;
		}

		$rekap = $this->Log_absensi_model->get_rekap_absensi($id);

		foreach ($rekap as &$r) {
			$r->hari = $this->convert_day($r->hari);
		}

		$data = [
			'pegawai' => $pegawai,
			'absensi' => $rekap,
		];

		$this->load->view('log_absensi/rekap_absensi_pegawai', $data);
	}

	function convert_day($day)
	{
		$days = [
			'Sunday' => 'Minggu',
			'Monday' => 'Senin',
			'Tuesday' => 'Selasa',
			'Wednesday' => 'Rabu',
			'Thursday' => 'Kamis',
			'Friday' => 'Jumat',
			'Saturday' => 'Sabtu'
		];

		return $days[$day] ?? $day;
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
		$this->form_validation->set_rules('url_foto_presensi', 'url foto presensi', 'trim');
		$this->form_validation->set_rules('url_dokumen', 'url dokumen', 'trim');

		$this->form_validation->set_rules('id_log_absensi', 'id_log_absensi', 'trim');
		$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
	}

}

/* End of file Log_absensi.php */
/* Location: ./application/controllers/Log_absensi.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2025-03-12 08:14:34 */
/* http://harviacode.com */
