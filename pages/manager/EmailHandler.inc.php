<?php

/**
 * EmailHandler.inc.php
 *
 * Copyright (c) 2003-2004 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.manager
 *
 * Handle requests for email management functions. 
 *
 * $Id$
 */

class EmailHandler extends ManagerHandler {

	/**
	 * Display a list of the sections within the current journal.
	 */
	function emails() {
		parent::validate();
		parent::setupTemplate(true);

		$journal = &Request::getJournal();
		$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplates = &$emailTemplateDao->getEmailTemplates($journal->getJournalId());
		
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy', array(array('manager', 'manager.journalManagement')));
		$templateMgr->assign('emailTemplates', $emailTemplates);
		$templateMgr->display('manager/emails/emails.tpl');
	}
	
	/**
	 * Display form to create/edit a section.
	 * @param $args array optional, if set the first parameter is the key of the email template to edit
	 */
	function editEmail($args = array()) {
		parent::validate();
		parent::setupTemplate(true);
		
		import('manager.form.EmailTemplateForm');
		
		$emailTemplateForm = &new EmailTemplateForm(!isset($args) || empty($args) ? null : $args[0]);
		$emailTemplateForm->initData();
		$emailTemplateForm->display();
	}
	
	/**
	 * Save changes to a section.
	 */
	function updateEmail() {
		parent::validate();
		
		import('manager.form.EmailTemplateForm');
		
		$emailTemplateForm = &new EmailTemplateForm(Request::getUserVar('emailKey'));
		$emailTemplateForm->readInputData();
		
		if ($emailTemplateForm->validate()) {
			$emailTemplateForm->execute();
			Request::redirect('manager/emails');
			
		} else {
			parent::setupTemplate(true);
			$emailTemplateForm->display();
		}
	}
	
	/**
	 * Reset an email to default.
	 * @param $args array first parameter is the key of the email to reset
	 */
	function resetEmail($args) {
		parent::validate();
		
		if (isset($args) && !empty($args)) {
			$journal = &Request::getJournal();
		
			$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplateDao->deleteEmailTemplateByKey($args[0], $journal->getJournalId());
		}
		
		Request::redirect('manager/emails');
	}
	
	/**
	 * resets all email templates associated with the journal.
	 */
	function resetAllEmails() {
		parent::validate();
		
		$journal = &Request::getJournal();
		
		$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplates = $emailTemplateDao->getEmailTemplates($journal->getJournalId());
			
		foreach ($emailTemplates as $emailTemplate) {
			$emailTemplateDao->deleteEmailTemplateByKey($emailTemplate->getEmailKey(), $journal->getJournalId());
		}
		
		Request::redirect('manager/emails');
	}
	
	/**
	 * disables an email template.
	 * @param $args array first parameter is the key of the email to disable
	 */
	function disableEmail($args) {
		parent::validate();
		
		if (isset($args) && !empty($args)) {
			$journal = &Request::getJournal();
		
			$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplate = $emailTemplateDao->getEmailTemplate($args[0], $journal->getJournalId());
			
			if ($emailTemplate->getCanDisable()) {
				$emailTemplate->setEnabled(0);
				
				if ($emailTemplate->getJournalId() == null) {
					$emailTemplate->setJournalId($journal->getJournalId());
				}
		
				if ($emailTemplate->getEmailId() != null) {
					$emailTemplateDao->updateEmailTemplate($emailTemplate);
					$emailId = $emailTemplate->getEmailId();
				} else {
					$emailTemplateDao->insertEmailTemplate($emailTemplate);
					$emailId = $emailTemplateDao->getInsertEmailId();
				}
			}
		}
		
		Request::redirect('manager/emails');
	}
	
	/**
	 * enables an email template.
	 * @param $args array first parameter is the key of the email to enable
	 */
	function enableEmail($args) {
		parent::validate();
		
		if (isset($args) && !empty($args)) {
			$journal = &Request::getJournal();
		
			$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplate = $emailTemplateDao->getEmailTemplate($args[0], $journal->getJournalId());
			
			if ($emailTemplate->getCanDisable()) {
				$emailTemplate->setEnabled(1);
				
				if ($emailTemplate->getEmailId() != null) {
					$emailTemplateDao->updateEmailTemplate($emailTemplate);
					$emailId = $emailTemplate->getEmailId();
				} else {
					$emailTemplateDao->insertEmailTemplate($emailTemplate);
					$emailId = $emailTemplateDao->getInsertEmailId();
				}
			}
		}
		
		Request::redirect('manager/emails');
	}
}
?>
