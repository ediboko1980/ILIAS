<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilMailViewGUI
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class ilMailViewGUI {
	public function __construct($a_view_title, $a_backlink, $a_subject, $a_text
							   , $a_frame = null, $a_image_path = null, $a_image_style = null
							   , $a_attachments = array(), $a_recipient = null, $a_cc = null
							   , $a_bcc = null, $a_resend_link = null) {
		global $lng;

		$this->lng = &$lng;

		$this->view_title = $a_view_title;
		$this->backlink = $a_backlink;
		$this->subject = $a_subject;
		$this->text = $a_text;
		$this->frame = $a_frame;
		$this->image_path = $a_image_path;
		$this->image_style = $a_image_style?$a_image_style:"";
		$this->attachments = $a_attachments;
		$this->recipient = $a_recipient;
		$this->cc = $a_cc;
		$this->bcc = $a_bcc;
		$this->resend_link = $a_resend_link;
	}

	private function fillRow($tpl, $name, $value) {	
		$tpl->setCurrentBlock("row_bl");
		$tpl->setVariable("NAME", $name);
		$tpl->setVariable("VALUE", $value);
		$tpl->parseCurrentBlock();
	}

	public function getHTML() {
		$tpl = new ilTemplate("tpl.mail_view.html", true, true, "Services/Mailing");

		$tpl->setVariable("BACKLINK", $this->backlink);
		$tpl->setVariable("BACKLINK_TITLE", $this->lng->txt("back"));
		$tpl->setVariable("TITLE", $this->view_title);
		
		if ($this->resend_link) {
			$tpl->setCurrentBlock("resend");
			$tpl->setVariable("RESEND_TITLE", $this->lng->txt("gev_resend_mail"));
			$tpl->setVariable("RESEND_TARGET", $this->resend_link);
			$tpl->parseCurrentBlock();
		}

		if ($this->recipient !== null) {
			$this->fillRow($tpl, $this->lng->txt("recipient"), $this->recipient);
		}
		if($this->cc !== null) {
			$this->fillRow($tpl, $this->lng->txt("cc_recipient"), $this->cc);
		}
		if($this->bcc !== null) {
			$this->fillRow($tpl, $this->lng->txt("bcc_recipient"), $this->bcc);
		}

		
		if ($this->frame) {
			$msg = str_ireplace("[content]", $this->text, $this->frame);
			if ($this->image_path) {
				$msg = str_ireplace("[image]", "<img src='".$this->image_path."' style='".$this->image_style."' />", $msg);
			}
		}
		else {
			$msg = $this->text;
		}

		if (strpos("<p>", $msg) === false) { //html mails will contain a <p>, plain text won't
			$msg = str_replace("\n", "<br />", $msg);
		}

		$this->fillRow($tpl, $this->lng->txt("subject"), $this->subject);
		$this->fillRow($tpl, $this->lng->txt("content"), $msg);

		if (count($this->attachments) > 0) {
			$tpl->setCurrentBlock("attachment_row_bl");
			$tpl->setVariable("ATTACHMENT_TXT", $this->lng->txt("attachments"));

			foreach ($this->attachments as $attachment) {
				$tpl->setCurrentBlock("attachment_bl");
				$tpl->setVariable("ATTACHMENT_LINK", $attachment["link"]);
				$tpl->setVariable("ATTACHMENT_NAME", $attachment["name"]);
				$tpl->parseCurrentBlock();
			}

			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}
}

?>