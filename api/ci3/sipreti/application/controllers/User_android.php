<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class User_android extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('User_android_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$q = urldecode($this->input->get('q', TRUE));
		$start = intval($this->input->get('start'));

		if ($q <> '') {
			$config['base_url'] = base_url() . 'user_android/index.html?q=' . urlencode($q);
			$config['first_url'] = base_url() . 'user_android/index.html?q=' . urlencode($q);
		} else {
			$config['base_url'] = base_url() . 'user_android/index.html';
			$config['first_url'] = base_url() . 'user_android/index.html';
		}

		$config['per_page'] = 10;
		$config['page_query_string'] = TRUE;
		$config['total_rows'] = $this->User_android_model->total_rows($q, TRUE);
		$user_android = $this->User_android_model->get_limit_data($config['per_page'], $start, $q, TRUE);

		$this->load->library('pagination');
		$this->pagination->initialize($config);

		$data = array(
			'user_android_data' => $user_android,
			'q' => $q,
			'pagination' => $this->pagination->create_links(),
			'total_rows' => $config['total_rows'],
			'start' => $start,
		);
		$this->load->view('user_android/user_android_list', $data);
	}

	public function read($id)
	{
		$row = $this->User_android_model->get_by_id($id);
		if ($row && empty($row->deleted_at)) {
			$data = array(
				'id_user_android' => $row->id_user_android,
				'id_pegawai' => $row->id_pegawai,
				'username' => $row->username,
				'password' => $row->password,
				'email' => $row->email,
				'no_hp' => $row->no_hp,
				'valid_hp' => $row->valid_hp,
				'imei' => $row->imei,
				'created_at' => $row->created_at,
				'updated_at' => $row->updated_at,
				'deleted_at' => $row->deleted_at,
			);
			$this->load->view('user_android/user_android_read', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('user_android'));
		}
	}

	public function create()
	{
		$data = array(
			'button' => 'Create',
			'action' => site_url('user_android/create_action'),
			'id_user_android' => set_value('id_user_android'),
			'id_pegawai' => set_value('id_pegawai'),
			'username' => set_value('username'),
			'password' => set_value('password'),
			'email' => set_value('email'),
			'no_hp' => set_value('no_hp'),
			'valid_hp' => set_value('valid_hp'),
			'imei' => set_value('imei'),
		);
		$this->load->view('user_android/user_android_form', $data);
	}

	public function create_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->create();
		} else {
			$hashedPassword = password_hash($this->input->post('password', TRUE), PASSWORD_BCRYPT);

			$data = array(
				'id_pegawai' => $this->input->post('id_pegawai', TRUE),
				'username' => $this->input->post('username', TRUE),
				'password' => $hashedPassword,
				'email' => $this->input->post('email', TRUE),
				'no_hp' => $this->input->post('no_hp', TRUE),
				'valid_hp' => $this->input->post('valid_hp', TRUE),
				'imei' => $this->input->post('imei', TRUE),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => NULL,
				'deleted_at' => NULL,
			);

			$this->User_android_model->insert($data);
			$this->session->set_flashdata('message', 'Create Record Success');
			redirect(site_url('user_android'));
		}
	}

	public function update($id)
	{
		$row = $this->User_android_model->get_by_id($id);

		if ($row) {
			$data = array(
				'button' => 'Update',
				'action' => site_url('user_android/update_action'),
				'id_user_android' => set_value('id_user_android', $row->id_user_android),
				'id_pegawai' => set_value('id_pegawai', $row->id_pegawai),
				'username' => set_value('username', $row->username),
				'password' => set_value('password', $row->password),
				'email' => set_value('email', $row->email),
				'no_hp' => set_value('no_hp', $row->no_hp),
				'valid_hp' => set_value('valid_hp', $row->valid_hp),
				'imei' => set_value('imei', $row->imei),
			);
			$this->load->view('user_android/user_android_form', $data);
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('user_android'));
		}
	}

	public function update_action()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->update($this->input->post('id_user_android', TRUE));
		} else {
			$hashedPassword = password_hash($this->input->post('password', TRUE), PASSWORD_BCRYPT);

			$data = array(
				'id_pegawai' => $this->input->post('id_pegawai', TRUE),
				'username' => $this->input->post('username', TRUE),
				'password' => $hashedPassword,
				'email' => $this->input->post('email', TRUE),
				'no_hp' => $this->input->post('no_hp', TRUE),
				'valid_hp' => $this->input->post('valid_hp', TRUE),
				'imei' => $this->input->post('imei', TRUE),
				'updated_at' => date('Y-m-d H:i:s'),
			);

			$this->User_android_model->update($this->input->post('id_user_android', TRUE), $data);
			$this->session->set_flashdata('message', 'Update Record Success');
			redirect(site_url('user_android'));
		}
	}

	public function delete($id)
	{
		$row = $this->User_android_model->get_by_id($id);

		if ($row) {
			$this->User_android_model->delete($id);
			$this->session->set_flashdata('message', 'Delete Record Success');
			redirect(site_url('user_android'));
		} else {
			$this->session->set_flashdata('message', 'Record Not Found');
			redirect(site_url('user_android'));
		}
	}

	public function create_api()
	{
		$this->_rules();

		if ($this->form_validation->run() == FALSE) {
			$this->create();
		} else {
			$hashedPassword = password_hash($this->input->post('password', TRUE), PASSWORD_BCRYPT);

			$data = array(
				'id_pegawai' => $this->input->post('id_pegawai', TRUE),
				'username' => $this->input->post('username', TRUE),
				'password' => $hashedPassword,
				'email' => $this->input->post('email', TRUE),
				'no_hp' => $this->input->post('no_hp', TRUE),
				'valid_hp' => $this->input->post('valid_hp', TRUE),
				'imei' => $this->input->post('imei', TRUE),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => NULL,
				'deleted_at' => NULL,
			);

			// Simpan ke database
			$this->User_android_model->insert($data);

			$response = array(
				'status' => 200,
				'message' => 'User berhasil ditambahkan',
				'data' => array(
					'username' => $this->input->post('username', TRUE),
					'email' => $this->input->post('email', TRUE),
					'no_hp' => $this->input->post('no_hp', TRUE),
					'imei' => $this->input->post('imei', TRUE),
					'valid_hp' => $this->input->post('valid_hp', TRUE),
				)
			);

			$this->output
				->set_content_type('application/json')
				->set_status_header(200)
				->set_output(json_encode($response));
		}
	}

	public function login_api()
	{
		$email = $this->input->post('email', TRUE);
		$password = $this->input->post('password', TRUE);

		// Cek apakah email tersedia di database
		$user = $this->User_android_model->get_by_email($email);

		if ($user) {
			// Verifikasi password yang diinputkan dengan yang ada di database
			if (password_verify($password, $user->password)) {
				$response = array(
					'status' => 200,
					'message' => 'Login berhasil',
					'data' => array(
						'id_user_android' => $user->id_user_android,
						// 'id_pegawai' => $user->id_pegawai,
						'username' => $user->username,
						'email' => $user->email,
						'no_hp' => $user->no_hp,
						// 'imei' => $user->imei,
						// 'valid_hp' => $user->valid_hp,
					)
				);
			} else {
				$response = array(
					'status' => 401,
					'message' => 'Password salah'
				);
			}
		} else {
			$response = array(
				'status' => 404,
				'message' => 'Email tidak ditemukan'
			);
		}

		$this->output
			->set_content_type('application/json')
			->set_status_header($response['status'])
			->set_output(json_encode($response));
	}


	public function _rules()
	{
		$this->form_validation->set_rules('id_pegawai', 'id pegawai', 'trim|required');
		$this->form_validation->set_rules('username', 'username', 'trim|required');
		$this->form_validation->set_rules('password', 'password', 'trim|required');
		$this->form_validation->set_rules('email', 'email', 'trim|required');
		$this->form_validation->set_rules('no_hp', 'no hp', 'trim|required');
		$this->form_validation->set_rules('valid_hp', 'valid hp', 'trim|required');
		$this->form_validation->set_rules('imei', 'imei', 'trim|required');

		$this->form_validation->set_rules('id_user_android', 'id_user_android', 'trim');
		$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
	}

}

/* End of file User_android.php */
/* Location: ./application/controllers/User_android.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2025-03-12 08:02:00 */
/* http://harviacode.com */
