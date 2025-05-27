<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Pegawai extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Pegawai_model');
		$this->load->model('Jabatan_model');
		$this->load->model('Unit_kerja_model');

		$this->load->library('form_validation');
	}

	public function index()
	{
		$q = urldecode($this->input->get('q', TRUE));
		$start = intval($this->input->get('start'));

		if ($q <> '') {
			$config['base_url'] = base_url() . 'pegawai/index.html?q=' . urlencode($q);
			$config['first_url'] = base_url() . 'pegawai/index.html?q=' . urlencode($q);
		} else {
			$config['base_url'] = base_url() . 'pegawai/index.html';
			$config['first_url'] = base_url() . 'pegawai/index.html';
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
		$this->load->view('pegawai/pegawai_list', $data);
	}

	public function read($id)
	{
		$row = $this->Pegawai_model->get_by_id($id);
		if ($row && empty($row->deleted_at)) {
			$data = array(
				'id_pegawai' => $row->id_pegawai,
				'id_jabatan' => $row->id_jabatan,
				'id_unit_kerja' => $row->id_unit_kerja,
				'nip' => $row->nip,
				'nama' => $row->nama,
				'url_foto' => $row->url_foto,
				'jabatan_options' => $this->Jabatan_model->get_all(),
				'unit_kerja_options' => $this->Unit_kerja_model->get_all(),
			);
			$this->load->view('pegawai/pegawai_read', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('pegawai'));
		}
	}

	public function read_api($id)
	{
		$row = $this->Pegawai_model->get_by_id_api($id);

		if ($row && empty($row['deleted_at'])) {
			$data = array(
				'id_pegawai' => $row['id_pegawai'],
				'nip' => $row['nip'],
				'nama' => $row['nama'],
				'url_foto' => $row['url_foto'],
				'nama_jabatan' => $row['nama_jabatan'],

				// Data Unit Kerja
				'nama_unit_kerja' => $row['nama_unit_kerja'],
				'alamat_unit_kerja' => $row['alamat'],
				'lattitude' => $row['lattitude'],
				'longitude' => $row['longitude'],

				// Data Radius Absen
				'ukuran_radius' => $row['ukuran'],

				// Data Face Embeddings sebagai array
				'face_embeddings' => $row['face_embeddings'],
			);

			header('Content-Type: application/json');
			echo json_encode($data);
		} else {
			$this->session->set_flashdata('message', 'Data pegawai tidak ditemukan');
			redirect(site_url('pegawai'));
		}
	}


	public function create()
	{
		$data = array(
			'button' => 'Create',
			'action' => site_url('pegawai/create_action'),
			'id_pegawai' => set_value('id_pegawai'),
			'id_jabatan' => set_value('id_jabatan'),
			'id_unit_kerja' => set_value('id_unit_kerja'),
			'nip' => set_value('nip'),
			'nama' => set_value('nama'),
			'url_foto' => set_value('url_foto'),
			'jabatan_options' => $this->Jabatan_model->get_all(),
			'unit_kerja_options' => $this->Unit_kerja_model->get_all(),
		);
		$this->load->view('pegawai/pegawai_form', $data);
	}

	public function create_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->create();
		} else {

			$nip = $this->input->post('nip', TRUE);

			$existing = $this->Pegawai_model->count_by_nip($nip);
			if ($existing) {
				$this->session->set_flashdata('message', 'NIP sudah digunakan');
				redirect(site_url('pegawai'));
				return;
			}

			$upload_path = './uploads/foto_pegawai/';
			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0777, true);
			}

			$config['upload_path'] = $upload_path;
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['max_size'] = 10240; // 10MB
			$config['encrypt_name'] = FALSE;

			$foto = null;
			if (!empty($_FILES['url_foto']['name'])) {
				$extension = pathinfo($_FILES['url_foto']['name'], PATHINFO_EXTENSION);
				$original_name = str_replace(' ', '_', pathinfo($_FILES['url_foto']['name'], PATHINFO_FILENAME));
				$new_filename = date('YmdHis') . '_' . $original_name;
				$config['file_name'] = $new_filename;

				$this->load->library('upload', $config);
				$this->upload->initialize($config);

				if ($this->upload->do_upload('url_foto')) {
					$upload_data = $this->upload->data();
					$foto = $upload_data['file_name'];
				} else {
					$this->session->set_flashdata('message', $this->upload->display_errors());
					redirect(site_url('pegawai/create'));
					return;
				}
			}

			$data = array(
				'id_jabatan' => $this->input->post('id_jabatan', TRUE),
				'id_unit_kerja' => $this->input->post('id_unit_kerja', TRUE),
				'nip' => $this->input->post('nip', TRUE),
				'nama' => $this->input->post('nama', TRUE),
				'url_foto' => $foto,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => NULL,
				'deleted_at' => NULL,
			);

			$this->Pegawai_model->insert($data);
			$this->session->set_flashdata('message', 'Create Record Success');
			redirect(site_url('pegawai'));
		}
	}

	public function update($id)
	{
		$row = $this->Pegawai_model->get_by_id($id);

		if ($row) {
			$data = array(
				'button' => 'Update',
				'action' => site_url('pegawai/update_action'),
				'id_pegawai' => set_value('id_pegawai', $row->id_pegawai),
				'id_jabatan' => set_value('id_jabatan', $row->id_jabatan),
				'id_unit_kerja' => set_value('id_unit_kerja', $row->id_unit_kerja),
				'nip' => set_value('nip', $row->nip),
				'nama' => set_value('nama', $row->nama),
				'url_foto' => set_value('url_foto', $row->url_foto),
				'jabatan_options' => $this->Jabatan_model->get_all(),
				'unit_kerja_options' => $this->Unit_kerja_model->get_all(),
			);
			$this->load->view('pegawai/pegawai_form', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('pegawai'));
		}
	}

	public function update_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->update($this->input->post('id_pegawai', TRUE));
		} else {
			$upload_path = './uploads/foto_pegawai/';
			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0777, true); // Buat folder jika belum ada
			}

			$config['upload_path'] = $upload_path;
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['max_size'] = 10240; // 10MB
			$config['encrypt_name'] = FALSE;

			$foto = $this->input->post('existing_foto');

			if (!empty($_FILES['url_foto']['name'])) {
				$extension = pathinfo($_FILES['url_foto']['name'], PATHINFO_EXTENSION);
				$original_name = str_replace(' ', '_', pathinfo($_FILES['url_foto']['name'], PATHINFO_FILENAME));
				$new_filename = date('YmdHis') . '_' . $original_name . '.' . $extension;
				$config['file_name'] = $new_filename;

				$this->load->library('upload', $config);
				$this->upload->initialize($config);

				if ($this->upload->do_upload('url_foto')) {
					// Hapus file lama jika ada
					if (!empty($foto) && file_exists($upload_path . $foto)) {
						unlink($upload_path . $foto);
					}

					$upload_data = $this->upload->data();
					$foto = $upload_data['file_name'];
				} else {
					$this->session->set_flashdata('message', $this->upload->display_errors());
					$this->update($this->input->post('id_pegawai', TRUE));
					return;
				}
			}

			$data = array(
				'id_jabatan' => $this->input->post('id_jabatan', TRUE),
				'id_unit_kerja' => $this->input->post('id_unit_kerja', TRUE),
				'nip' => $this->input->post('nip', TRUE),
				'nama' => $this->input->post('nama', TRUE),
				'url_foto' => $foto,
				'updated_at' => date('Y-m-d H:i:s'),
			);

			$this->Pegawai_model->update($this->input->post('id_pegawai', TRUE), $data);
			$this->session->set_flashdata('message', 'Update Record Success');
			redirect(site_url('pegawai'));
		}
	}

	public function delete($id)
	{
		$row = $this->Pegawai_model->get_by_id($id);

		if ($row) {
			$data = array(
				'deleted_at' => date('Y-m-d H:i:s'),
			);

			$this->Pegawai_model->update($id, $data);
			$this->session->set_flashdata('message', 'Delete Record Success');
			redirect(site_url('pegawai'));
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('pegawai'));
		}
	}

	public function validate_nip()
	{
		$nip = $this->input->get('nip', TRUE);

		if (!$nip) {
			$response = array(
				'status' => 400,
				'message' => 'NIP harus disertakan dalam request.'
			);
			$this->output
				->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode($response));
			return;
		}

		$pegawai = $this->Pegawai_model->get_by_nip($nip);

		if ($pegawai) {
			$response = array(
				'status' => 200,
				'message' => 'NIP ditemukan.',
				'data' => $pegawai
			);
			$this->output
				->set_content_type('application/json')
				->set_status_header(200)
				->set_output(json_encode($response));
		} else {
			$response = array(
				'status' => 404,
				'message' => 'NIP tidak dikenali.'
			);
			$this->output
				->set_content_type('application/json')
				->set_status_header(404)
				->set_output(json_encode($response));
		}
	}

	public function create_api()
	{
		$this->load->library(['upload', 'form_validation']);
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			echo json_encode(['error' => validation_errors()]);
			return;
		}

		$nip = $this->input->post('nip', TRUE);

		$pegawai_lama = $this->Pegawai_model->get_by_nip($nip);

		$upload_path = './uploads/foto_pegawai/';
		if (!is_dir($upload_path)) {
			mkdir($upload_path, 0777, true);
		}

		$config['upload_path'] = $upload_path;
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size'] = 10240;
		$config['encrypt_name'] = TRUE;

		$this->upload->initialize($config);

		$foto = null;
		if (!empty($_FILES['url_foto']['name'])) {
			if ($this->upload->do_upload('url_foto')) {
				$upload_data = $this->upload->data();
				$foto = $upload_data['file_name'];
			} else {
				echo json_encode(['error' => $this->upload->display_errors()]);
				return;
			}
		}

		$data = array(
			'id_jabatan' => $this->input->post('id_jabatan', TRUE),
			'id_unit_kerja' => $this->input->post('id_unit_kerja', TRUE),
			'nip' => $nip,
			'nama' => $this->input->post('nama', TRUE),
			'updated_at' => date('Y-m-d H:i:s'),
		);

		if ($foto !== null) {
			$data['url_foto'] = $foto;
		}

		if ($pegawai_lama) {
			$this->Pegawai_model->update($pegawai_lama->id_pegawai, $data);
			$id_pegawai = $pegawai_lama->id_pegawai;
		} else {
			$data['created_at'] = date('Y-m-d H:i:s');
			$data['deleted_at'] = NULL;
			$id_pegawai = $this->Pegawai_model->insert_api($data);
		}

		echo json_encode([
			'message' => 'Data pegawai berhasil disimpan/diperbarui',
			'id_pegawai' => $id_pegawai
		]);
	}
	
	public function _rules()
	{
		$this->form_validation->set_rules('id_jabatan', 'id jabatan', 'trim|required');
		$this->form_validation->set_rules('id_unit_kerja', 'id unit kerja', 'trim|required');
		$this->form_validation->set_rules('nip', 'nip', 'trim|required');
		$this->form_validation->set_rules('nama', 'nama', 'trim|required');
		// $this->form_validation->set_rules('url_foto', 'url foto', 'trim|required');

		$this->form_validation->set_rules('id_pegawai', 'id_pegawai', 'trim');
		$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
	}

}

/* End of file Pegawai.php */
/* Location: ./application/controllers/Pegawai.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2025-03-12 07:46:51 */
/* http://harviacode.com */
