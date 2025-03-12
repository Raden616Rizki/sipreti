<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Unit_kerja extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Unit_kerja_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$q = urldecode($this->input->get('q', TRUE));
		$start = intval($this->input->get('start'));

		if ($q <> '') {
			$config['base_url'] = base_url() . 'unit_kerja/index.html?q=' . urlencode($q);
			$config['first_url'] = base_url() . 'unit_kerja/index.html?q=' . urlencode($q);
		} else {
			$config['base_url'] = base_url() . 'unit_kerja/index.html';
			$config['first_url'] = base_url() . 'unit_kerja/index.html';
		}

		$config['per_page'] = 10;
		$config['page_query_string'] = TRUE;
		$config['total_rows'] = $this->Unit_kerja_model->total_rows($q, TRUE);
		$unit_kerja = $this->Unit_kerja_model->get_limit_data($config['per_page'], $start, $q, TRUE);

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$data = array(
			'unit_kerja_data' => $unit_kerja,
			'q' => $q,
			'pagination' => $this->pagination->create_links(),
			'total_rows' => $config['total_rows'],
			'start' => $start,
		);
		$this->load->view('unit_kerja/unit_kerja_list', $data);
	}

	public function read($id)
	{
		$row = $this->Unit_kerja_model->get_by_id($id);
		if ($row && empty($row->deleted_at)) {
			$data = array(
				'id_unit_kerja' => $row->id_unit_kerja,
				'id_radius' => $row->id_radius,
				'nama_unit_kerja' => $row->nama_unit_kerja,
				'alamat' => $row->alamat,
				'lattitude' => $row->lattitude,
				'longitude' => $row->longitude,
				'created_at' => $row->created_at,
				'updated_at' => $row->updated_at,
				'deleted_at' => $row->deleted_at,
			);
			$this->load->view('unit_kerja/unit_kerja_read', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('unit_kerja'));
		}
	}

	public function create()
	{
		$data = array(
			'button' => 'Create',
			'action' => site_url('unit_kerja/create_action'),
			'id_unit_kerja' => set_value('id_unit_kerja'),
			'id_radius' => set_value('id_radius'),
			'nama_unit_kerja' => set_value('nama_unit_kerja'),
			'alamat' => set_value('alamat'),
			'lattitude' => set_value('lattitude'),
			'longitude' => set_value('longitude'),
		);
		$this->load->view('unit_kerja/unit_kerja_form', $data);
	}

	public function create_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->create();
		} else {
			$data = array(
				'id_radius' => $this->input->post('id_radius', TRUE),
				'nama_unit_kerja' => $this->input->post('nama_unit_kerja', TRUE),
				'alamat' => $this->input->post('alamat', TRUE),
				'lattitude' => $this->input->post('lattitude', TRUE),
				'longitude' => $this->input->post('longitude', TRUE),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => NULL,
				'deleted_at' => NULL,
			);

			$this->Unit_kerja_model->insert($data);
			$this->session->set_flashdata('message', 'Create Record Success');
			redirect(site_url('unit_kerja'));
		}
	}

	public function update($id)
	{
		$row = $this->Unit_kerja_model->get_by_id($id);

		if ($row) {
			$data = array(
				'button' => 'Update',
				'action' => site_url('unit_kerja/update_action'),
				'id_unit_kerja' => set_value('id_unit_kerja', $row->id_unit_kerja),
				'id_radius' => set_value('id_radius', $row->id_radius),
				'nama_unit_kerja' => set_value('nama_unit_kerja', $row->nama_unit_kerja),
				'alamat' => set_value('alamat', $row->alamat),
				'lattitude' => set_value('lattitude', $row->lattitude),
				'longitude' => set_value('longitude', $row->longitude),
			);
			$this->load->view('unit_kerja/unit_kerja_form', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('unit_kerja'));
		}
	}

	public function update_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->update($this->input->post('id_unit_kerja', TRUE));
		} else {
			$data = array(
				'id_radius' => $this->input->post('id_radius', TRUE),
				'nama_unit_kerja' => $this->input->post('nama_unit_kerja', TRUE),
				'alamat' => $this->input->post('alamat', TRUE),
				'lattitude' => $this->input->post('lattitude', TRUE),
				'longitude' => $this->input->post('longitude', TRUE),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			$this->Unit_kerja_model->update($this->input->post('id_unit_kerja', TRUE), $data);
			$this->session->set_flashdata('message', 'Update Record Success');
			redirect(site_url('unit_kerja'));
		}
	}

	public function delete($id)
	{
		$row = $this->Unit_kerja_model->get_by_id($id);

		if ($row) {
			$data = array(
				'deleted_at' => date('Y-m-d H:i:s'),
			);

			$this->Unit_kerja_model->update($id, $data);
			$this->session->set_flashdata('message', 'Delete Record Success');
			redirect(site_url('unit_kerja'));
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('unit_kerja'));
		}
	}

	public function _rules()
	{
		$this->form_validation->set_rules('id_radius', 'id radius', 'trim|required');
		$this->form_validation->set_rules('nama_unit_kerja', 'nama unit kerja', 'trim|required');
		$this->form_validation->set_rules('alamat', 'alamat', 'trim|required');
		$this->form_validation->set_rules('lattitude', 'lattitude', 'trim|required|numeric');
		$this->form_validation->set_rules('longitude', 'longitude', 'trim|required|numeric');

		$this->form_validation->set_rules('id_unit_kerja', 'id_unit_kerja', 'trim');
		$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
	}

}

/* End of file Unit_kerja.php */
/* Location: ./application/controllers/Unit_kerja.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2025-03-12 07:17:53 */
/* http://harviacode.com */
