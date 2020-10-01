<?php
/**
 * @copyright 2012-2020 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
namespace Application\Controllers;

use Application\Models\Person;
use Application\Models\PersonTable;
use Application\Models\DepartmentTable;

use Blossom\Classes\Block;
use Blossom\Classes\Controller;
use Blossom\Classes\Template;

class UsersController extends Controller
{
	public function index()
	{
        global $ACL;

		$depts  = new DepartmentTable();
		$people = new PersonTable();
		$users  = $people->search(array_merge($_GET, ['user_account'=>true]));
		$vars   = [
            'users'                 => $users,
            'departments'           => $depts->find(),
            'roles'                 => $ACL->getRoles(),
            'authenticationMethods' => Person::getAuthenticationMethods()
		];
		$this->template->title  = $this->template->_(['user', 'users', 100]);
		$this->template->blocks = [ new Block('users/findForm.inc', $vars) ];
	}

	public function update()
	{
		if (isset($_REQUEST['person_id'])) {
			// Load the user for editing
			try {
				$user = new Person($_REQUEST['person_id']);
			}
			catch (\Exception $e) {
				$_SESSION['errorMessages'][] = $e;
				header('Location: '.BASE_URL.'/users');
				exit();
			}
		}
		else {
			$user = new Person();
		}

		// Handle POST data
		if (isset($_POST['username'])) {
			try {
 				$user->handleUpdateUserAccount($_POST);
				$user->save();
				header('Location: '.BASE_URL.'/users');
				exit();
			}
			catch (\Exception $e) {
				$_SESSION['errorMessages'][] = $e;
			}
		}

		// Display the form
		if ($user->getId()) {
			$this->template->blocks[] = new Block(
				'people/personInfo.inc',
				['person'=>$user,'disableButtons'=>true]
			);
		}
		$this->template->title = $user->getId()
            ? $this->template->_('create_account')
            : $this->template->_('edit_account');
		$this->template->blocks[] = new Block('users/updateUserForm.inc', ['person'=>$user]);
	}

	/**
	 * Deletes a Person's user account information
	 */
	public function delete()
	{
		$person = new Person($_GET['person_id']);
		$person->deleteUserAccount();
		try {
			$person->save();
		}
		catch (\Exception $e) {
			$_SESSION['errorMessages'][] = $e;
		}

		header('Location: '.BASE_URL.'/users');
	}
}
