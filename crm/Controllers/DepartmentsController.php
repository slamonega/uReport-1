<?php
/**
 * @copyright 2012-2016 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Application\Controllers;

use Application\Models\Department;
use Application\Models\DepartmentTable;

use Blossom\Classes\Block;
use Blossom\Classes\Controller;
use Blossom\Classes\Template;

class DepartmentsController extends Controller
{
	public function index()
	{
		$table = new DepartmentTable();
		$departmentList = $table->find();

		$this->template->blocks[] = new Block('departments/departmentList.inc', ['departmentList'=>$departmentList]);
	}

	public function view()
	{
		$department = new Department($_GET['department_id']);

		$this->template->blocks[] = new Block('departments/departmentInfo.inc', ['department'=>$department]);
	}

	public function update()
	{
		// Load the department for editing
		if (isset($_REQUEST['department_id']) && $_REQUEST['department_id']) {
			try {
				$department = new Department($_REQUEST['department_id']);
			}
			catch (\Exception $e) {
				$_SESSION['errorMessages'][] = $e;
				header('Location: '.BASE_URL.'/departments');
				exit();
			}
		}
		else {
			$department = new Department();
		}

		if (isset($_POST['name'])) {
			try {
				$department->handleUpdate($_POST);
				$department->save();

				header('Location: '.BASE_URL.'/departments/view?department_id='.$department->getId());
				exit();
			}
			catch (\Exception $e) {
				$_SESSION['errorMessages'][] = $e;
			}
		}

		$this->template->blocks[] = new Block('departments/updateDepartmentForm.inc', [
            'department' => $department,
            'action'     => BASE_URI.'/departments/update',
            'return_url' => BASE_URI.'/departments'
        ]);
	}

	public function delete()
	{
		try {
			$department = new Department($_GET['department_id']);
			$department->delete();
		}
		catch (\Exception $e) {
			$_SESSION['errorMessages'][] = $e;
		}

		header('Location: '.BASE_URL.'/departments');
		exit();
	}
}
