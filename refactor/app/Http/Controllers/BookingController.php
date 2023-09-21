<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Helpers\IUserRoles;
use DTApi\Repository\BookingRepository;

/**
 * @author Shaarif<m.shaarif@xintsolutions.com>
 * Class BookingController
 * @package DTApi\Http\Controllers
 */

class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    private $_bookingRepository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->_bookingRepository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if($user_id   = $request->get('user_id'))
            $response = $this->_bookingRepository->getUsersJobs($user_id);
        elseif($request->__authenticatedUser->user_type == IUserRoles::ADMIN_ROLE || $request->__authenticatedUser->user_type == IUserRoles::SUPER_ADMIN_ROLE)
            $response = $this->_bookingRepository->getAll($request);
        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->_bookingRepository->with('translatorJobRel.user')->find($id);
        return response($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data     = $request->all();
        $response = $this->_bookingRepository->store($request->__authenticatedUser, $data);
        return response($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data       = $request->all();
        $cuser      = $request->__authenticatedUser;
        $response   = $this->_bookingRepository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $data             = $request->all();
        $response = $this->_bookingRepository->storeJobEmail($data);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {
            $response = $this->_bookingRepository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }
        return null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->_bookingRepository->acceptJob($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = $this->_bookingRepository->acceptJobWithId($data, $user);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->_bookingRepository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data     = $request->all();
        $response = $this->_bookingRepository->endJob($data);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function customerNotCall(Request $request)
    {
        $data     = $request->all();
        $response = $this->_bookingRepository->customerNotCall($data);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->_bookingRepository->getPotentialJobs($user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        # distance set
        if (isset($data['distance']) && $data['distance'] != "")
            $distance = $data['distance'];
        else
            $distance = "";
        # time set
        if (isset($data['time']) && $data['time'] != "")
            $time = $data['time'];
        else
            $time = "";

        # Job id set
        if (isset($data['jobid']) && $data['jobid'] != "")
            $jobid = $data['jobid'];

        # session time set
        if (isset($data['session_time']) && $data['session_time'] != "")
            $session = $data['session_time'];
        else
            $session = "";

        # flag set
        if ($data['flagged'] == 'true') {
            if($data['admincomment'] == '') return "Please, add comment";
            $flagged = 'yes';
        } else
            $flagged = 'no';

        # manual handled set
        if ($data['manually_handled'] == 'true')
            $manually_handled = 'yes';
        else
            $manually_handled = 'no';

        # is by admin flag set
        if ($data['by_admin'] == 'true')
            $by_admin = 'yes';
        else
            $by_admin = 'no';

        # admin commitment set
        if (isset($data['admincomment']) && $data['admincomment'] != "")
            $admincomment = $data['admincomment'];
         else
            $admincomment = "";

        if ($time || $distance)
            $affectedRows = Distance::where('job_id', $jobid)
                            ->update(['distance' => $distance, 'time' => $time]);

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin)
            $affectedRows1 = Job::where('id', '=', $jobid)
                            ->update($this->_filterUpdateJobRequest($admincomment,$flagged,$session,$manually_handled,$by_admin));

        return response('Record updated!');
    }

    /**
     * @param $adminComment
     * @param $flagged
     * @param $session
     * @param $manuallyHandled
     * @param $byAdmin
     * @return array
     */
    private function _filterUpdateJobRequest($adminComment,$flagged,$session,$manuallyHandled,$byAdmin)
    {
        return [
            'admin_comments'    => $adminComment,
            'flagged'           => $flagged,
            'session_time'      => $session,
            'manually_handled'  => $manuallyHandled,
            'by_admin'          => $byAdmin
        ];
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function reOpen(Request $request)
    {
        $data     = $request->all();
        $response = $this->_bookingRepository->reopen($data);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function resendNotifications(Request $request)
    {
        $data     = $request->all();
        $job      = $this->_bookingRepository->find($data['jobid']);
        $job_data = $this->_bookingRepository->jobToData($job);
        $this->_bookingRepository->sendNotificationTranslator($job, $job_data, '*');
        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job  = $this->_bookingRepository->find($data['jobid']);
        $this->_bookingRepository->jobToData($job);
        try {
            $this->_bookingRepository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
