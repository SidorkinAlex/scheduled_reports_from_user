<?php
/**
 * Created by PhpStorm.
 * User: seedteam
 * Date: 16.08.20
 * Time: 14:47
 */
class CustomAORScheduledReportJob implements RunnableSchedulerJob
{
    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }

    public function run($data)
    {
        global $current_user, $timedate;

        $bean = BeanFactory::getBean('AOR_Scheduled_Reports', $data);
        if (empty($bean->from_user)) {
            $report = $bean->get_linked_beans('aor_report', 'AOR_Reports');
            if ($report) {
                $report = $report[0];
            } else {
                return false;
            }
            $html = "<h1>{$report->name}</h1>" . $report->build_group_report();
            $html .= <<<EOF
        <style>
        h1{
            color: black;
        }
        .list
        {
            font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;font-size: 12px;
            background: #fff;margin: 45px;width: 480px;border-collapse: collapse;text-align: left;
        }
        .list th
        {
            font-size: 14px;
            font-weight: normal;
            color: black;
            padding: 10px 8px;
            border-bottom: 2px solid black;
        }
        .list td
        {
            padding: 9px 8px 0px 8px;
        }
        </style>
EOF;
            $emailObj = new Email();
            $defaults = $emailObj->getSystemDefaultEmail();
            $mail = new SugarPHPMailer();

            $mail->setMailerForSystem();
            $mail->IsHTML(true);
            $mail->From = $defaults['email'];
            isValidEmailAddress($mail->From);
            $mail->FromName = $defaults['name'];
            $mail->Subject = from_html($bean->name);
            $mail->Body = $html;
            $mail->prepForOutbound();
            $success = true;
            $emails = $bean->get_email_recipients();
            foreach ($emails as $email_address) {
                $mail->ClearAddresses();
                $mail->AddAddress($email_address);
                $success = $mail->Send() && $success;
            }
        } else {

            $report = $bean->get_linked_beans('aor_report', 'AOR_Reports');
            if ($report) {
                $report = $report[0];
            } else {
                return false;
            }


            $emails = $bean->get_email_recipients();
            foreach ($emails as $email_address) {
                $uu= new UserUtils();

                $current_user->retrieve($uu->getUserbyEmail($email_address));
                $html = "<h1>{$report->name}</h1>" . $report->build_group_report();
                $html .= <<<EOF
        <style>
        h1{
            color: black;
        }
        .list
        {
            font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;font-size: 12px;
            background: #fff;margin: 45px;width: 480px;border-collapse: collapse;text-align: left;
        }
        .list th
        {
            font-size: 14px;
            font-weight: normal;
            color: black;
            padding: 10px 8px;
            border-bottom: 2px solid black;
        }
        .list td
        {
            padding: 9px 8px 0px 8px;
        }
        </style>
EOF;
                $current_user->retrieve('1');
                $emailObj = new Email();
                $defaults = $emailObj->getSystemDefaultEmail();
                $mail = new SugarPHPMailer();

                $mail->setMailerForSystem();
                $mail->IsHTML(true);
                $mail->From = $defaults['email'];
                isValidEmailAddress($mail->From);
                $mail->FromName = $defaults['name'];
                $mail->Subject = from_html($bean->name);
                $mail->Body = $html;
                $mail->prepForOutbound();
                $success = true;
                $mail->ClearAddresses();
                $mail->AddAddress($email_address);
                $success = $mail->Send() && $success;
            }

        }
        $bean->last_run = $timedate->getNow()->asDb(false);
        $bean->save();
        return true;
    }
}
