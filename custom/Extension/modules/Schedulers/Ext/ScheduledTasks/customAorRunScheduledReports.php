<?php
/**
 * Created by PhpStorm.
 * User: seedteam
 * Date: 16.08.20
 * Time: 14:51
 */
$job_strings[] = 'customAorRunScheduledReports';

function customAorRunScheduledReports()
{
    require_once 'include/SugarQueue/SugarJobQueue.php';
    require_once 'custom/modules/Users/UserUtils.php';

    $date = new DateTime();//Ensure we check all schedules at the same instant
    foreach (BeanFactory::getBean('AOR_Scheduled_Reports')->get_full_list() as $scheduledReport) {
        if ($scheduledReport->status != 'active') {
            continue;
        }
        try {
            $shouldRun = $scheduledReport->shouldRun($date);
        } catch (Exception $ex) {
            LoggerManager::getLogger()->warn('aorRunScheduledReports: id: ' . $scheduledReport->id . ' got exception. code: ' . $ex->getCode() . ', message: ' . $ex->getMessage());
            $shouldRun = false;
        }
        if ($shouldRun) {
            if (empty($scheduledReport->aor_report_id)) {
                continue;
            }
            $job = new SchedulersJob();
            $job->name = "Scheduled report - {$scheduledReport->name} on {$date->format('c')}";
            $job->data = $scheduledReport->id;
            $job->target = "class::AORScheduledReportJob";
            $job->assigned_user_id = 1;
            $jq = new SugarJobQueue();
            $jq->submitJob($job);
        }
    }
    return true;
}