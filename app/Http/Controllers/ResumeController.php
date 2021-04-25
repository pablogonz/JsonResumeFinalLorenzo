<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ResumeController extends Controller
{
    /**
     * Create and return the needed validation
     * to make 100% we confirm the Schema mentioned
     * in https://jsonresume.org/schema/ when we store with "POST"
     * or do full update with "PUT"
     */
    private function GetValidation(Request $request, $required = true)
    {
        return Validator::make($request->resume, [
            "basics" => [Rule::requiredIf($required), 'array'],
            "basics.email" => [Rule::requiredIf($required), 'email:rfc,dns', $required ?'unique:App\Models\Resume,Email':''],
            "basics.location" => [Rule::requiredIf($required), 'array'],
            "basics.profiles" => [Rule::requiredIf($required), 'array'],
            "work" => [Rule::requiredIf($required), "array"],
            "work.*.highlights" => [Rule::requiredIf($required), "array"],
            "volunteer" => [Rule::requiredIf($required), "array"],
            "volunteer.*.highlights" => [Rule::requiredIf($required), "array"],
            "education" => [Rule::requiredIf($required), "array"],
            "education.*.courses" => [Rule::requiredIf($required), "array"],
            "awards" => [Rule::requiredIf($required), "array"],
            "publications" => [Rule::requiredIf($required), "array"],
            "skills" => [Rule::requiredIf($required), "array"],
            "skills.*.keywords" => [Rule::requiredIf($required), "array"],
            "languages" => [Rule::requiredIf($required), "array"],
            "interests" => [Rule::requiredIf($required), "array"],
            "interests.*.keywords" => [Rule::requiredIf($required), "array"],
            "references" => [Rule::requiredIf($required), "array"]
        ], [
            "basics.array" => "The :attribute section must be a valid Json Object.",
            "basics.email" => "The email section must be a valid email address.",
            "basics.email.unique" => "It looks like you already have a resume created with this email try 'GET' to see it or 'PATCH' to edit it.",
            "basics.location.array" => "The :attribute section must be a valid Json Object.",
            "basics.profiles.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "work.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "work.*.highlights.array" => "The work highlights section must be a valid Array Of highlights.",
            "volunteer.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "volunteer.*.highlights.array" => "The volunteer highlights section must be a valid Array Of highlights.",
            "education.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "education.*.courses.array" => "The education courses section must be a valid Array Of courses.",
            "awards.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "publications.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "skills.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "skills.*.keywords.array" => "The skills keywords section must be a valid Array Of keywords.",
            "languages.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "interests.array" => "The :attribute section must be a valid Json Array Of Objects.",
            "interests.*.keywords.array" => "The interests keywords section must be a valid Array Of keywords.",
            "references.array" => "The :attribute section must be a valid Json Array Of Objects."
        ]);
    }

    /**
     * Reorder a given array to respect the given keynames.
     */
    private function array_reorder_keys(&$array, $keynames)
    {
        if (empty($array) || !is_array($array) || empty($keynames)) return;
        if (!is_array($keynames)) $keynames = explode(',', $keynames);
        if (!empty($keynames)) $keynames = array_reverse($keynames);
        foreach ($keynames as $n) {
            if (array_key_exists($n, $array)) {
                $newarray = array($n => $array[$n]); //copy the node before unsetting
                unset($array[$n]); //remove the node
                $array = $newarray + array_filter($array); //combine copy with filtered array
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function showFullResume(): Response
    {
        // Get the Resume of the given Email from the url parameters
        $res = Resume::where('Email', request('email'))->get(['Resume']);
        /**
         * If we get results and $res not empty
         */
        if ($res->isNotEmpty()) {
            // We Greb the first row since each email belonge to one Resume
            $res = $res->first();
            // We Access the Resume Json file from database result
            $result = $res->Resume;
            // Then we arrange the result depends on the keynames orders
            $this->array_reorder_keys($result,
                'basics,work,volunteer,education,
                awards,publications,skills,languages,interests,references');
            // And finally we return the Json as a response in post man
            return response($result);
        } else
            // If we don't get a result from database we show this message as a response
            return response(['success' => 'sorry we did not find any resume belong to that email try create a new one']);
    }

    /**
     * Store a newly created resource in database.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Validate the Schema of the resume before storing it
        $validator = $this->GetValidation($request);
        /**
         * if the validation fails we return a message
         * to informe what's wrong with the Schema provided
         */
        if ($validator->fails()) {
            return response($validator->errors());
        }
        $result = [];
        /**
         * Dynamicly get the information provided
         * then store them into a table for later use
         */
        foreach ($request->resume as $key => $value) {
            $result[$key] = $value;
        }
        /**
         * Make a control on the database table
         * to assure nothing with be added to database
         * unless there is no database exception will accure
         */
        return DB::transaction(function () use ($result) {
            Resume::create([
                'Email' => $result['basics']['email'],
                'Resume' => $result
            ]);
            return response(['success' => 'your Resume has been saved successfully !']);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param $section
     * @param null $subsection
     * @return Response
     */
    public function showSpecific($section, $subsection = null): Response
    {
        // Get the Resume of the given Email from the url parameters
        $res = Resume::where('Email', request('email'))->get(['Resume']);
        /**
         * If we get results and $res not empty
         */
        if ($res->isNotEmpty()) {
            // We Greb the first row since each email belonge to one Resume
            $res = $res->first();
            // We Access the Resume Json file from database result
            $result = $res->Resume;
            // We test we just return the $section, example "~/basics"
            if ($subsection == null) {
                $portionOfResume = $result[$section] ?? null;
            } // else if the $subsection exists in the url if yes we return it's value in an array,example "~/basics/profiles"
            else {
                $portionOfResume = $result[$section][$subsection] ?? null;
            }
            // We test if the indexes passed to the array exists else we show message instade of exception of Undefined variable
            return $portionOfResume != null ? response($portionOfResume) : response(['success' => 'ops! you probably made a Typo, this Section dosen\'t exist']);
        } else
            // If we don't get a result from database we show this message as a response
            return response(['success' => 'sorry we did not find any resume belong to that email try create a new one']);
    }

    /**
     * Update "PUT" the specified resource in database.
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        // Validate the Schema of the resume before updating it
        $validator = $this->GetValidation($request);
        /**
         * if the validation fails we return a message
         * to informe what's wrong with the Schema provided
         */
        if ($validator->fails()) {
            return response($validator->errors());
        }
        $result = [];
        /**
         * Dynamicly get the information provided
         * then perform a full update "PUT" them
         */
        foreach ($request->resume as $key => $value) {
            $result[$key] = $value;
        }
        // We Get the old Resume from the database that belongs to that email
        $res = Resume::where('Email', request('email'))->get();
        if ($res->isNotEmpty()) {
            // We Greb the first row since each email belonge to one Resume
            $res = $res->first();
            // Updating new Values
            $res->Resume = $result;
            $res->Email = $result['basics']['email'];
            /**
             * The isDirty method determines if any of the model's attributes have been changed since the model was retrieved
             * if not no need to update
             */
            if ($res->isDirty()) {
                /**
                 * Make a control on the database table
                 * to assure nothing with be added to database
                 * unless there is no database exception will accure
                 */
                return DB::transaction(function () use ($res) {
                    $res->save();
                    return response(['success' => 'your Resume has been saved successfully !']);
                });
            }
        } else
            // If we don't get a result from database we show this message as a response
            return response(['success' => 'sorry we did not find any resume belong to that email try create a new one']);
    }

    /**
     * Update "PATCH" the specified Portion of the resource in database.
     *
     * @param Request $request
     * @param $section
     * @param null $subsection
     * @return Response
     */
    public function updateSpecific(Request $request, $section, $subsection = null)
    {
        // Validate the Schema of the resume before updating it
        $validator = $this->GetValidation($request, false);
        /**
         * if the validation fails we return a message
         * to informe what's wrong with the Schema provided
         */
        if ($validator->fails()) {
            return response($validator->errors());
        }
        $result = [];
        /**
         * Dynamicly get the information provided
         * then perform a full update "PUT" them
         */
        foreach ($request->resume as $key => $value) {
            $result[$key] = $value;
        }
        // We Get the old Resume from the database that belongs to that email
        $res = Resume::where('Email', request('email'))->get();
        if ($res->isNotEmpty()) {
            // We Greb the first row since each email belonge to one Resume
            $res = $res->first();
            // Copy all the resume to a new array so we don't override all the resume
            $newResume = $res->Resume;
            // Updating new Values

            // We test we just return the $section, example "~/basics"
            if ($subsection == null) {
                $portionOfResume = $result[$section] ?? null;
            }
            /** else if the $subsection exists in the url if yes we return it's value in an array,
             * example "~/basics/profiles"
             */
            else {
                $portionOfResume = $result[$section][$subsection] ?? null;
            }
            // Check if the email is present
            $email = $result['basics']['email'] ?? null;
            if ($email != null) {
                // if yes we want to update the email too
                $res->Email = $result['basics']['email'];
            }
            // We test if the indexes passed to the array exists else we show message instade of exception of Undefined variable
            if ($portionOfResume != null) {
                if ($subsection == null)
                    $newResume[$section] = $result[$section];
                else
                    $newResume[$section][$subsection] = $result[$section][$subsection];
                // after that we change the values that got updated in the newResume to the original Resume
                $res->Resume = $newResume;
                /**
                 * The isDirty method determines if any of the model's attributes have been changed since the model was retrieved
                 * if not no need to update
                 */
                if ($res->isDirty()) {
                    /**
                     * Make a control on the database table
                     * to assure nothing with be added to database
                     * unless there is no database exception will accure
                     */
                    return DB::transaction(function () use ($res) {
                        $res->save();
                        return response(['success' => 'your Resume section has been saved successfully!']);
                    });
                }
            } else {
                return response(['success' => 'ops! you probably made a Typo, this Section dosen\'t exist']);
            }
        } else
            // If we don't get a result from database we show this message as a response
            return response(['success' => 'sorry we did not find any resume belong to that email try create a new one']);
    }

    /**
     * Remove the specified resource from database.
     *
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        // we perfore the delete inside the DB::Transaction so there will be no database errors
        return DB::transaction(function () {
            $res = Resume::where('Email', request('email'))->delete();
            // Depends on if it was deleted or not we show a response
            if ($res) {
                return response(['success' => 'your Resume has been deleted successfully!']);
            } else {
                return response(['success' => 'ops, no Resume found with this email ' . request('email') . '!']);
            }
        });
    }
}
