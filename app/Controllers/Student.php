<?php

namespace App\Controllers;

use App\Models\StudentModel;

class Student extends BaseController
{
    private StudentModel $studentModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $query = trim((string) $this->request->getGet('q'));
        $sort = (string) $this->request->getGet('sort');
        $dir = strtolower((string) $this->request->getGet('dir')) === 'desc' ? 'desc' : 'asc';

        $allowedSortFields = ['name', 'roll_no', 'branch', 'degree', 'created_at'];
        if (! in_array($sort, $allowedSortFields, true)) {
            $sort = 'id';
        }

        $builder = $this->studentModel;

        if ($query !== '') {
            $builder = $builder
                ->groupStart()
                ->like('name', $query)
                ->orLike('roll_no', $query)
                ->orLike('branch', $query)
                ->orLike('degree', $query)
                ->groupEnd();
        }

        $students = $builder->orderBy($sort, $dir)->paginate(10);

        return view('students/list', [
            'students' => $students,
            'pager'    => $this->studentModel->pager,
            'query'    => $query,
            'sort'     => $sort,
            'dir'      => $dir,
        ]);
    }

    public function create()
    {
        return view('students/create', [
            'validation' => service('validation'),
        ]);
    }

    public function store()
    {
        $rules = [
            'name'    => 'required|min_length[2]|max_length[100]',
            'email'   => 'required|valid_email|is_unique[students.email]',
            'roll_no' => 'required|max_length[50]|is_unique[students.roll_no]',
            'branch'  => 'required|in_list[CSE,IT,ECE,EEE,Mech,Civil]',
            'degree'  => 'required|in_list[B.E,B.Tech,M.E,M.Tech,MBA]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/student/create')->withInput()->with('validation', $this->validator);
        }

        $this->studentModel->insert([
            'name'    => trim((string) $this->request->getPost('name')),
            'email'   => trim((string) $this->request->getPost('email')),
            'roll_no' => trim((string) $this->request->getPost('roll_no')),
            'branch'  => trim((string) $this->request->getPost('branch')),
            'degree'  => trim((string) $this->request->getPost('degree')),
        ]);

        return redirect()->to('/')->with('success', 'Student added successfully.');
    }

    public function edit(int $id)
    {
        $student = $this->studentModel->find($id);

        if (! $student) {
            return redirect()->to('/')->with('error', 'Student not found.');
        }

        return view('students/edit', [
            'student'    => $student,
            'validation' => service('validation'),
        ]);
    }

    public function update(int $id)
    {
        $student = $this->studentModel->find($id);

        if (! $student) {
            return redirect()->to('/')->with('error', 'Student not found.');
        }

        $rules = [
            'name'    => 'required|min_length[2]|max_length[100]',
            'email'   => "required|valid_email|is_unique[students.email,id,{$id}]",
            'roll_no' => "required|max_length[50]|is_unique[students.roll_no,id,{$id}]",
            'branch'  => 'required|in_list[CSE,IT,ECE,EEE,Mech,Civil]',
            'degree'  => 'required|in_list[B.E,B.Tech,M.E,M.Tech,MBA]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/student/edit/' . $id)->withInput()->with('validation', $this->validator);
        }

        $this->studentModel->update($id, [
            'name'    => trim((string) $this->request->getPost('name')),
            'email'   => trim((string) $this->request->getPost('email')),
            'roll_no' => trim((string) $this->request->getPost('roll_no')),
            'branch'  => trim((string) $this->request->getPost('branch')),
            'degree'  => trim((string) $this->request->getPost('degree')),
        ]);

        return redirect()->to('/')->with('success', 'Student updated successfully.');
    }

    public function delete(int $id)
    {
        $student = $this->studentModel->find($id);

        if (! $student) {
            return redirect()->to('/')->with('error', 'Student not found.');
        }

        $this->studentModel->delete($id);

        return redirect()->to('/')->with('success', 'Student deleted successfully.');
    }
}
