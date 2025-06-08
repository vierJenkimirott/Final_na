<?php

namespace Database\Seeders;

use App\Models\OffenseCategory;
use App\Models\Severity;
use App\Models\ViolationType;
use Illuminate\Database\Seeder;

class ViolationsSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all categories and severities at once
        $categories = OffenseCategory::all()->keyBy('category_name');
        $severities = Severity::all()->keyBy('severity_name');

        $violationTypes = [
            // General Behavior
            'General Behavior' => [
                ['violation_name' => 'Cooking in the kitchen for personal purposes and without the permission from the staff.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Placing things in the fire exit and using it as a passageway.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Skipping a meal without a valid reason.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Disrespecting staff/students such as using foul languages, physical and verbal violence, bullying, backbiting, spreading gossip and name calling.', 'default_penalty' => 'WW', 'severity' => 'Medium'],
                ['violation_name' => 'Organizing a party without permission from the education team.', 'default_penalty' => 'WW', 'severity' => 'Medium'],
                ['violation_name' => 'Stealing personal belongings of fellow students, staff, and PN owned properties.', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Smoking and vaping inside and outside the PN premises.', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Engaging in gambling activities including E-gambling.', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Downloading or possessing inappropriate content (e.g., pornographic).', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Drinking alcoholic beverages.', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Using PN staff name for personal agenda.', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Joining or creating any fraternity/sorority organization.', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Having romantic relationships with fellow students and showing public displays of affection.', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Being pregnant or impregnating someone.', 'default_penalty' => 'Exp', 'severity' => 'Very High'],
                ['violation_name' => 'Using illegal drugs and engaging in substance abuse.', 'default_penalty' => 'Exp', 'severity' => 'Very High'],
                ['violation_name' => 'Having intimate/sexual relationships between staff/teachers and students.', 'default_penalty' => 'Exp', 'severity' => 'Very High'],
                ['violation_name' => 'Having sexual relationship between fellow PN students.', 'default_penalty' => 'Exp', 'severity' => 'Very High'],
            ],
            // Dress Code
            'Dress Code' => [
                ['violation_name' => 'Wearing heavy make-up except for special events and with moderation, provided that permission was given by the educators. Wearing earrings for the boy.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Dying hair with a bright color that does not match the natural hair.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Wearing inappropriate/unprofessional clothing (e.g., short shorts, miniskirt, spaghetti straps, see-through clothes, offensive prints, pajamas) and being topless for both boys and girls are forbidden.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Wearing PN t-shirt and school uniform outside specific activities.', 'default_penalty' => 'VW', 'severity' => 'Low'],
            ],
            // Room Rules
            'Room Rules' => [
                ['violation_name' => 'Speaking loudly, shouting and playing loud music inside the center.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Posting or vandalism on the room walls.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Sleeping in another room other than the one assigned.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Not maintaining hygiene (e.g., bedsheets and pillow cases must be washed every two weeks).', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Not turning off lights, faucet and electric fan when not in use.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Not maintaining room cleanliness such as a dirty and smelly bathroom and comfort room, mirror and sink is not properly cleaned, trash or clutter on the floor, overflowing trash in trash bins, unorganized beds, and etc.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Hanging clothes on the windows and balcony.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Bringing food or eating meals inside the room.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Boys entering girls room or vice versa except for emergencies.', 'default_penalty' => 'WW', 'severity' => 'Medium'],
            ],
            // Schedule
            'Schedule' => [
                ['violation_name' => 'Not submitting grades every semester.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Hanging out on the rooftop.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Falsifying logout/login time.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Turning on the aircon beyond the prescribed schedule.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Not respecting staff schedules and approaching them at inappropriate times.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Not filling in the auto evaluation every month.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Staying out past the 10:00PM curfew.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Not following the visitation schedule for parents and relatives.', 'default_penalty' => 'VW', 'severity' => 'Low'],
                ['violation_name' => 'Not following schedules given by the PN staff (class, tasks, activities, meals).', 'default_penalty' => 'WW', 'severity' => 'Medium'],
                ['violation_name' => 'Not participating or missing in PN activities and University classes without valid reason.', 'default_penalty' => 'WW', 'severity' => 'Medium'],
                ['violation_name' => 'Not respecting the going-out schedule.', 'default_penalty' => 'WW', 'severity' => 'Medium'],
                ['violation_name' => 'Not surrendering phones, laptops, and other gadgets on scheduled time.', 'default_penalty' => 'WW', 'severity' => 'Medium'],
                ['violation_name' => 'Going home without a valid reason (except for family emergencies like death or terminal illness).  Leaving the center without permission or a signed going-home waiver.', 'default_penalty' => 'Pro', 'severity' => 'High'],
            ],
            // Equipment
            'Equipment' => [
                ['violation_name' => 'Plugging in USBs, external hard drives, or personal gadgets without permission.', 'default_penalty' => 'WW', 'severity' => 'Medium'],
                ['violation_name' => 'Destroying or damaging PN properties.', 'default_penalty' => 'Pro', 'severity' => 'High'],
                ['violation_name' => 'Unauthorized use of PN-provide equipment. Downloading files, programs, or software in PN laptop without approval from the PN IT and training team. Browsing or accessing restricted websites and engaging in unauthorized online activities on PN/Personal equipment.', 'default_penalty' => 'Pro', 'severity' => 'High'],
            ],
            // Center Tasking
            'Center Tasking' => [
                ['violation_name' => 'Not participating in general cleaning, center tasking and routines.', 'default_penalty' => 'VW', 'severity' => 'Low'],
            ],
        ];

        foreach ($violationTypes as $categoryName => $types) {
            if (!isset($categories[$categoryName])) {
                continue;
            }
            $categoryId = $categories[$categoryName]->id;

            foreach ($types as $type) {
                if (!isset($severities[$type['severity']])) {
                    continue;
                }
                $severityId = $severities[$type['severity']]->id;

                ViolationType::updateOrCreate(
                    ['violation_name' => $type['violation_name']],
                    [
                        'offense_category_id' => $categoryId,
                        'default_penalty' => $type['default_penalty'],
                        'severity_id' => $severityId,
                    ]
                );
            }
        }
    }
}