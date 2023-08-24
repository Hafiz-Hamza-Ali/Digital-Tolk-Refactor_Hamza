<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $response = [];
        if ($user_id = $request->get("user_id")) {
            $response = $this->repository->getUsersJobs($user_id);
        } elseif (
            $request->__authenticatedUser->user_type == env("ADMIN_ROLE_ID") ||
            $request->__authenticatedUser->user_type ==
                env("SUPERADMIN_ROLE_ID")
        ) {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with("translatorJobRel.user")->find($id);

        return response($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->store(
            $request->__authenticatedUser,
            $data
        );

        return response($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob(
            $id,
            array_except($data, ["_token", "submit"]),
            $cuser
        );

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config("app.adminemail");
        $data = $request->all();

        $response = $this->repository->storeJobEmail($data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get("user_id")) {
            $response = $this->repository->getUsersJobsHistory(
                $user_id,
                $request
            );
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

        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $jobId = $request->get("job_id");
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJobWithId($jobId, $user);

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

        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response($response);
    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

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

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = $data["distance"] ?? "";
        $time = $data["time"] ?? "";
        $jobid = $data["jobid"] ?? "";
        $session = $data["session_time"] ?? "";
        $flagged = $data["flagged"] === "true";
        $manually_handled = $data["manually_handled"] === "true";
        $by_admin = $data["by_admin"] === "true";
        $admincomment = $data["admincomment"] ?? "";

        try {
            if ($time || $distance) {
                $this->updateDistance($jobid, $distance, $time);
            }

            if (
                $admincomment ||
                $session ||
                $flagged ||
                $manually_handled ||
                $by_admin
            ) {
                $this->updateJobAttributes(
                    $jobid,
                    $admincomment,
                    $session,
                    $flagged,
                    $manually_handled,
                    $by_admin
                );
            }

            return response("Record updated!");
        } catch (\Exception $e) {
            return response()->json([
                "error" => "An error occurred while updating the record.",
            ]);
        }
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $jobId = $data["jobid"];
        $job = $this->repository->find($jobId);

        if (!$job) {
            return response(["error" => "Job not found"], 404);
        }

        $jobData = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $jobData, "*");

        return response(["success" => "Push sent"]);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data["jobid"]);

        if (!$job) {
            return response(["error" => "Job not found"], 404);
        }
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(["success" => "SMS sent"]);
        } catch (\Exception $e) {
            return response(["success" => $e->getMessage()]);
        }
    }
}
