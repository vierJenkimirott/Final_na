<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ViolationsSeeder extends Seeder
{
    public function run(): void
    {
        $generalBehaviorCategoryId = DB::table('offense_categories')->where('category_name', 'General Behavior')->value('id');
        $dressCodeCategoryId = DB::table('offense_categories')->where('category_name', 'Dress Code')->value('id');
        $roomRulesCategoryId = DB::table('offense_categories')->where('category_name', 'Room Rules')->value('id');
        $scheduleCategoryId = DB::table('offense_categories')->where('category_name', 'Schedule')->value('id');
        $equipmentCategoryId = DB::table('offense_categories')->where('category_name', 'Equipment')->value('id');
        $centerTaskingCategoryId = DB::table('offense_categories')->where('category_name', 'Center Tasking')->value('id');


        $lowId = DB::table('severities')->where('severity_name', 'Low')->value('id');
        $mediumId = DB::table('severities')->where('severity_name', 'Medium')->value('id');
        $highId = DB::table('severities')->where('severity_name', 'High')->value('id');
        $veryHighId = DB::table('severities')->where('severity_name', 'Very High')->value('id');
 
        
        $violationTypes = [
            // General Behavior - Low Severity
            ['violation_name' => 'Cooking in the kitchen for personal purposes and without the permission from the staff.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Placing things in the fire exit and using it as a passageway.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Speaking loudly, shouting and playing loud music inside the center.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Skipping a meal without a valid reason.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Hanging out on the rooftop.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            
            // General Behavior - Medium Severity
            ['violation_name' => 'Disrespecting staff/students such as using foul languages, physical and verbal violence, bullying, backbiting, spreading gossip and name calling.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'WW', 'severity_id' => $mediumId],
            ['violation_name' => 'Organizing a party without permission from the education team.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'WW', 'severity_id' => $mediumId],
            
            // General Behavior - High Severity
            ['violation_name' => 'Stealing personal belongings of fellow students, staff, and PN owned properties.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Destroying or damaging PN properties.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Smoking and vaping inside and outside the PN premises.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Engaging in gambling activities including E-gambling.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Downloading or possessing inappropriate content (e.g., pornographic).', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Drinking alcoholic beverages.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Using PN staff name for personal agenda.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Joining or creating any fraternity/sorority organization.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Having romantic relationships with fellow students and showing public displays of affection.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            
            // General Behavior - Very High Severity
            ['violation_name' => 'Being pregnant or impregnating someone.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Exp', 'severity_id' => $veryHighId],
            ['violation_name' => 'Using illegal drugs and engaging in substance abuse.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Exp', 'severity_id' => $veryHighId],
            ['violation_name' => 'Having intimate/sexual relationships between staff/teachers and students.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Exp', 'severity_id' => $veryHighId],
            ['violation_name' => 'Having sexual relationship between fellow PN students.', 'offense_category_id' => $generalBehaviorCategoryId, 'default_penalty' => 'Exp', 'severity_id' => $veryHighId],

            // Dress Code - Low Severity
            ['violation_name' => 'Wearing heavy make-up except for special events and with moderation, provided that permission was given by the educators. Wearing earrings for the boy.', 'offense_category_id' => $dressCodeCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Dying hair with a bright color that does not match the natural hair.', 'offense_category_id' => $dressCodeCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Wearing inappropriate/unprofessional clothing (e.g., short shorts, miniskirt, spaghetti straps, see-through clothes, offensive prints, pajamas) and being topless for both boys and girls are forbidden.', 'offense_category_id' => $dressCodeCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Wearing PN t-shirt and school uniform outside specific activities.', 'offense_category_id' => $dressCodeCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],

            // Room Rules - Low Severity
            ['violation_name' => 'Speaking loudly, shouting and playing loud music inside the center.', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Posting or vandalism on the room walls.', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Sleeping in another room other than the one assigned.', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Not maintaining hygiene (e.g., bedsheets and pillow cases must be washed every two weeks).', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Not turning of lights, faucet and electric fan when not in use.', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Not maintaining room cleanliness such as a dirty and smelly bathroom and comfort room, mirror and sink is not properly cleaned, trash or clutter on the floor, overflowing trash in trash bins, unorganized beds, and etc.', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Hanging clothes on the windows and balcony.', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Bringing food or eating meals inside the room.', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            
            // Room Rules - Medium Severity
            ['violation_name' => 'Boys entering girls room or vice versa except for emergencies.', 'offense_category_id' => $roomRulesCategoryId, 'default_penalty' => 'WW', 'severity_id' => $mediumId],

            // Schedule - Low Severity
            ['violation_name' => 'Not submitting grades every semester.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Hanging out on the rooftop.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Falsifying logout/login time.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Turning on the aircon beyond the prescribed schedule.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Not respecting staff schedules and approaching them at inappropriate times.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Not filling in the auto evaluation every month.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Staying out past the 10:00PM curfew.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            ['violation_name' => 'Not following the visitation schedule for parents and relatives.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
            
            // Schedule - Medium Severity
            ['violation_name' => 'Not following schedules given by the PN staff (class, tasks, activities, meals).', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'WW', 'severity_id' => $mediumId],
            ['violation_name' => 'Not participating or missing in PN activities and University classes without valid reason.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'WW', 'severity_id' => $mediumId],
            ['violation_name' => 'Not respecting the going-out schedule.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'WW', 'severity_id' => $mediumId],
            ['violation_name' => 'Not surrendering phones, laptops, and other gadgets on scheduled time.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'WW', 'severity_id' => $mediumId],
            
            // Schedule - High Severity
            ['violation_name' => 'Going home without a valid reason (except for family emergencies like death or terminal illness).  Leaving the center without permission or a signed going-home waiver.', 'offense_category_id' => $scheduleCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],

            // Equipment - Medium Severity
            ['violation_name' => 'Plugging in USBs, external hard drives, or personal gadgets without permission.', 'offense_category_id' => $equipmentCategoryId, 'default_penalty' => 'WW', 'severity_id' => $mediumId],
            
            // Equipment - High Severity
            ['violation_name' => 'Destroying or damaging PN properties.', 'offense_category_id' => $equipmentCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],
            ['violation_name' => 'Unauthorized use of PN-provide equipment. Downloading files, programs, or software in PN laptop without approval from the PN IT and training team. Browsing or accessing restricted websites and engaging in unauthorized online activities on PN/Personal equipment.', 'offense_category_id' => $equipmentCategoryId, 'default_penalty' => 'Pro', 'severity_id' => $highId],

            // Center Tasking - Low Severity
            ['violation_name' => 'Not participating in general cleaning, center tasking and routines.', 'offense_category_id' => $centerTaskingCategoryId, 'default_penalty' => 'VW', 'severity_id' => $lowId],
        ];

        foreach ($violationTypes as $type) {
            DB::table('violation_types')->insert($type);
        }
    }
} 