<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Vektor_pegawai extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Vektor_pegawai_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$q = urldecode($this->input->get('q', TRUE));
		$start = intval($this->input->get('start'));

		if ($q <> '') {
			$config['base_url'] = base_url() . 'vektor_pegawai/index.html?q=' . urlencode($q);
			$config['first_url'] = base_url() . 'vektor_pegawai/index.html?q=' . urlencode($q);
		} else {
			$config['base_url'] = base_url() . 'vektor_pegawai/index.html';
			$config['first_url'] = base_url() . 'vektor_pegawai/index.html';
		}

		$config['per_page'] = 10;
		$config['page_query_string'] = TRUE;
		$config['total_rows'] = $this->Vektor_pegawai_model->total_rows($q, TRUE);
		$vektor_pegawai = $this->Vektor_pegawai_model->get_limit_data($config['per_page'], $start, $q, TRUE);

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$data = array(
			'vektor_pegawai_data' => $vektor_pegawai,
			'q' => $q,
			'pagination' => $this->pagination->create_links(),
			'total_rows' => $config['total_rows'],
			'start' => $start,
		);
		$this->load->view('vektor_pegawai/vektor_pegawai_list', $data);
	}

	public function read($id)
	{
		$row = $this->Vektor_pegawai_model->get_by_id($id);
		if ($row && empty($row->deleted_at)) {
			$data = array(
				'id_vektor_pegawai' => $row->id_vektor_pegawai,
				'id_pegawai' => $row->id_pegawai,
				'face_embeddings' => $row->face_embeddings,
				'url_foto' => $row->url_foto,
				'created_at' => $row->created_at,
				'updated_at' => $row->updated_at,
				'deleted_at' => $row->deleted_at,
			);
			$this->load->view('vektor_pegawai/vektor_pegawai_read', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('vektor_pegawai'));
		}
	}

	public function create()
	{
		$data = array(
			'button' => 'Create',
			'action' => site_url('vektor_pegawai/create_action'),
			'id_vektor_pegawai' => set_value('id_vektor_pegawai'),
			'id_pegawai' => set_value('id_pegawai'),
			'face_embeddings' => set_value('face_embeddings'),
			'url_foto' => set_value('url_foto'),
		);
		$this->load->view('vektor_pegawai/vektor_pegawai_form', $data);
	}

	public function create_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->create();
		} else {
			$data = array(
				'id_pegawai' => $this->input->post('id_pegawai', TRUE),
				'face_embeddings' => $this->input->post('face_embeddings', TRUE),
				'url_foto' => $this->input->post('url_foto', TRUE),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => NULL,
				'deleted_at' => NULL,
			);

			$this->Vektor_pegawai_model->insert($data);
			$this->session->set_flashdata('message', 'Create Record Success');
			redirect(site_url('vektor_pegawai'));
		}
	}

	public function update($id)
	{
		$row = $this->Vektor_pegawai_model->get_by_id($id);

		if ($row) {
			$data = array(
				'button' => 'Update',
				'action' => site_url('vektor_pegawai/update_action'),
				'id_vektor_pegawai' => set_value('id_vektor_pegawai', $row->id_vektor_pegawai),
				'id_pegawai' => set_value('id_pegawai', $row->id_pegawai),
				'face_embeddings' => set_value('face_embeddings', $row->face_embeddings),
				'url_foto' => set_value('url_foto', $row->url_foto),
			);
			$this->load->view('vektor_pegawai/vektor_pegawai_form', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('vektor_pegawai'));
		}
	}

	public function update_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->update($this->input->post('id_vektor_pegawai', TRUE));
		} else {
			$data = array(
				'id_pegawai' => $this->input->post('id_pegawai', TRUE),
				'face_embeddings' => $this->input->post('face_embeddings', TRUE),
				'url_foto' => $this->input->post('url_foto', TRUE),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			$this->Vektor_pegawai_model->update($this->input->post('id_vektor_pegawai', TRUE), $data);
			$this->session->set_flashdata('message', 'Update Record Success');
			redirect(site_url('vektor_pegawai'));
		}
	}

	public function delete($id)
	{
		$row = $this->Vektor_pegawai_model->get_by_id($id);

		if ($row) {
			$data = array(
				'deleted_at' => date('Y-m-d H:i:s'),
			);

			$this->Vektor_pegawai_model->update($id, $data);
			$this->session->set_flashdata('message', 'Delete Record Success');
			redirect(site_url('vektor_pegawai'));
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('vektor_pegawai'));
		}
	}

	public function _rules()
	{
		$this->form_validation->set_rules('id_pegawai', 'id pegawai', 'trim|required');
		$this->form_validation->set_rules('face_embeddings', 'face embeddings', 'trim|required');
		$this->form_validation->set_rules('url_foto', 'url foto', 'trim|required');

		$this->form_validation->set_rules('id_vektor_pegawai', 'id_vektor_pegawai', 'trim');
		$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
	}

}

/* End of file Vektor_pegawai.php */
/* Location: ./application/controllers/Vektor_pegawai.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2025-03-12 08:26:56 */
/* http://harviacode.com */
