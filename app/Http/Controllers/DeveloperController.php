<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Developer;
use Carbon\Carbon;

class DeveloperController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $file = $request->file('developers');
            $content = file_get_contents($file->path());
            $lines = explode("\n", $content);

            // Clear existing records
            Developer::truncate();
            
            foreach ($lines as $line) {
                $data = explode(',', $line);
                if (count($data) == 2) {
                    Developer::create([
                        'name' => trim($data[0]),
                        'date_of_birth' => trim($data[1])
                    ]);
                }
            }
            
            return response()->json(['message' => 'Developers uploaded successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while uploading'], 500);
        }
    }

    public function getCakeDays(Request $request)
    {
        $year = $request->input('year', now()->year);
        $developers = Developer::orderBy('date_of_birth')->get();
        $cakeDays = $this->calculateCakeDays($developers, $year);
        return response()->json($cakeDays);
    }
    
    private function calculateCakeDays($developers, $year)
    {
        $cakeDays = [];
        $holidays = $this->getHolidays($year);
        $lastCakeDay = null;
    
        $birthdayQueue = $this->prepareBirthdayQueue($developers, $year, $holidays);
    
        while (!empty($birthdayQueue)) {
            $currentBirthday = array_shift($birthdayQueue);
            $nextBirthday = $birthdayQueue[0] ?? null;
    
            $cakeInfo = $this->processBirthday($currentBirthday, $nextBirthday, $lastCakeDay, $holidays, $birthdayQueue);
    
            if ($cakeInfo) {
                $cakeDays[] = $cakeInfo;
                $lastCakeDay = Carbon::parse($cakeInfo['date']);
            }
        }
    
        return $cakeDays;
    }
    
    private function prepareBirthdayQueue($developers, $year, $holidays)
    {
        $queue = [];
        foreach ($developers as $developer) {
            $birthday = Carbon::parse($developer->date_of_birth)->setYear($year);
            $cakeDay = $this->getNextWorkingDay($birthday, $holidays);
            $queue[] = [
                'date' => $cakeDay->format('Y-m-d'),
                'name' => $developer->name
            ];
        }
        usort($queue, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        return $queue;
    }
    
    private function processBirthday($currentBirthday, $nextBirthday, $lastCakeDay, $holidays, &$birthdayQueue)
    {
        $currentDate = Carbon::parse($currentBirthday['date']);
        
        // Check if it's a cake-free day
        if ($lastCakeDay && $lastCakeDay->addDay()->equalTo($currentDate)) {
            $currentDate = $this->getNextWorkingDay($currentDate, $holidays);
        }
    
        $cakeInfo = [
            'date' => $currentDate->format('Y-m-d'),
            'smallCakes' => 1,
            'largeCakes' => 0,
            'people' => [$currentBirthday['name']]
        ];
    
        // Check for consecutive birthdays
        if ($nextBirthday && Carbon::parse($nextBirthday['date'])->equalTo($currentDate->copy()->addDay())) {
            $nextWorkingDay = $this->getNextWorkingDay($currentDate->copy()->addDay(), $holidays);
            $cakeInfo = [
                'date' => Carbon::parse($nextBirthday['date'])->format('Y-m-d'),
                'smallCakes' => 0,
                'largeCakes' => 1,
                'people' => [$currentBirthday['name'], $nextBirthday['name']]
            ];
            
            array_shift($birthdayQueue); // Remove the next birthday as it's been processed
        }

        // Check for same birthdays
        if ($nextBirthday && Carbon::parse($nextBirthday['date'])->equalTo($currentDate)) {
            $nextWorkingDay = $this->getNextWorkingDay($currentDate->copy()->addDay(), $holidays);
            $cakeInfo = [
                'date' => $nextWorkingDay->format('Y-m-d'),
                'smallCakes' => 0,
                'largeCakes' => 1,
                'people' => [$currentBirthday['name'], $nextBirthday['name']]
            ];
            
            array_shift($birthdayQueue); // Remove the next birthday as it's been processed
        }
    
        return $cakeInfo;
    }
    
    private function getNextWorkingDay($date, $holidays)
    {
        $date = $date->copy();

        // If the date is a weekend, move to the next Monday
        if ($date->isWeekend()) {
            $date->next(Carbon::MONDAY);
        }

        // Now, always move to the next day (to account for the day off)
        $date->addDay();

        // Keep moving forward until we find a working day
        while ($date->isWeekend() || in_array($date->format('Y-m-d'), $holidays)) {
            $date->addDay();
        }

        return $date;
    }

    private function getHolidays($year)
    {
        return [
            Carbon::create($year, 12, 25)->format('Y-m-d'), // Christmas Day
            Carbon::create($year, 12, 26)->format('Y-m-d'), // Boxing Day
            Carbon::create($year, 1, 1)->format('Y-m-d'),   // New Year's Day
        ];
    }
}
