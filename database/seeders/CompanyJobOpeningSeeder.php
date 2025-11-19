<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CompanyJobOpening;

class CompanyJobOpeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobOpenings = [
            [
                'title' => 'Senior Full Stack Developer',
                'department' => 'Engineering',
                'location' => 'Mumbai',
                'type' => 'full-time',
                'experience_level' => '4-6 years',
                'description' => 'Build and scale our security management platform using React, Laravel, and cloud technologies. You will be responsible for developing new features, optimizing performance, and ensuring code quality across our entire application stack.',
                'requirements' => ['React.js', 'Laravel/PHP', 'MySQL', 'AWS', 'Git', 'REST APIs'],
                'salary_range' => '₹12-18 LPA',
                'status' => 'active',
                'contact_email' => 'tech-hiring@rajsecurity.in',
                'priority' => 3,
                'application_deadline' => '2024-03-15',
            ],
            [
                'title' => 'DevOps Engineer',
                'department' => 'Engineering',
                'location' => 'Bangalore',
                'type' => 'full-time',
                'experience_level' => '3-5 years',
                'description' => 'Manage cloud infrastructure, CI/CD pipelines, and ensure 99.9% uptime for our security platform. Work with containerization, orchestration, and monitoring solutions.',
                'requirements' => ['AWS/Azure', 'Docker', 'Kubernetes', 'Jenkins', 'Linux', 'Terraform'],
                'salary_range' => '₹10-15 LPA',
                'status' => 'active',
                'contact_email' => 'tech-hiring@rajsecurity.in',
                'priority' => 2,
                'application_deadline' => '2024-02-28',
            ],
            [
                'title' => 'UI/UX Designer',
                'department' => 'Design',
                'location' => 'Remote',
                'type' => 'full-time',
                'experience_level' => '3-4 years',
                'description' => 'Design intuitive interfaces for security professionals across web and mobile platforms. Create user-centered design solutions that simplify complex security workflows.',
                'requirements' => ['Figma', 'Adobe Creative Suite', 'User Research', 'Prototyping', 'Design Systems'],
                'salary_range' => '₹8-12 LPA',
                'status' => 'active',
                'contact_email' => 'design-hiring@rajsecurity.in',
                'priority' => 1,
                'application_deadline' => '2024-03-01',
            ],
            [
                'title' => 'Product Manager',
                'department' => 'Product',
                'location' => 'Mumbai',
                'type' => 'full-time',
                'experience_level' => '5-7 years',
                'description' => 'Drive product strategy and roadmap for India\'s leading security management platform. Work closely with engineering, design, and business teams to deliver innovative solutions.',
                'requirements' => ['Product Strategy', 'User Research', 'Data Analysis', 'Security Industry Knowledge', 'Agile/Scrum'],
                'salary_range' => '₹15-22 LPA',
                'status' => 'active',
                'contact_email' => 'product-hiring@rajsecurity.in',
                'priority' => 2,
                'application_deadline' => '2024-03-10',
            ],
            [
                'title' => 'Sales Executive - Enterprise',
                'department' => 'Sales',
                'location' => 'Delhi',
                'type' => 'full-time',
                'experience_level' => '2-4 years',
                'description' => 'Build relationships with security agencies and enterprises across North India. Drive revenue growth through strategic partnerships and client acquisition.',
                'requirements' => ['B2B Sales', 'Security Industry', 'Hindi/English', 'Customer Relationship', 'CRM Tools'],
                'salary_range' => '₹6-10 LPA + Incentives',
                'status' => 'active',
                'contact_email' => 'sales-hiring@rajsecurity.in',
                'priority' => 1,
                'application_deadline' => '2024-02-25',
            ],
            [
                'title' => 'Customer Success Manager',
                'department' => 'Customer Success',
                'location' => 'Pune',
                'type' => 'full-time',
                'experience_level' => '3-5 years',
                'description' => 'Ensure customer success and satisfaction with our security management solutions. Drive product adoption, reduce churn, and expand accounts.',
                'requirements' => ['Customer Success', 'SaaS Experience', 'Communication', 'Problem Solving', 'Data Analysis'],
                'salary_range' => '₹8-12 LPA',
                'status' => 'active',
                'contact_email' => 'cs-hiring@rajsecurity.in',
                'priority' => 1,
                'application_deadline' => '2024-03-05',
            ],
            [
                'title' => 'Frontend Developer (React)',
                'department' => 'Engineering',
                'location' => 'Hyderabad',
                'type' => 'full-time',
                'experience_level' => '2-4 years',
                'description' => 'Build responsive and interactive user interfaces for our security management dashboard using React and modern frontend technologies.',
                'requirements' => ['React.js', 'JavaScript/TypeScript', 'CSS/SCSS', 'Responsive Design', 'Git'],
                'salary_range' => '₹8-12 LPA',
                'status' => 'active',
                'contact_email' => 'tech-hiring@rajsecurity.in',
                'priority' => 1,
                'application_deadline' => '2024-02-20',
            ],
            [
                'title' => 'Data Analyst',
                'department' => 'Engineering',
                'location' => 'Bangalore',
                'type' => 'full-time',
                'experience_level' => '1-3 years',
                'description' => 'Analyze security data, create insights, and build dashboards to help our clients make data-driven decisions about their security operations.',
                'requirements' => ['SQL', 'Python/R', 'Excel', 'Tableau/PowerBI', 'Statistics'],
                'salary_range' => '₹6-10 LPA',
                'status' => 'inactive',
                'contact_email' => 'data-hiring@rajsecurity.in',
                'priority' => 1,
                'application_deadline' => '2024-03-20',
            ],
            [
                'title' => 'Mobile App Developer (Flutter)',
                'department' => 'Engineering',
                'location' => 'Remote',
                'type' => 'contract',
                'experience_level' => '3-5 years',
                'description' => 'Develop cross-platform mobile applications for security personnel using Flutter. Focus on real-time communication and offline capabilities.',
                'requirements' => ['Flutter', 'Dart', 'Mobile Development', 'REST APIs', 'Firebase'],
                'salary_range' => '₹60-80k/month',
                'status' => 'active',
                'contact_email' => 'mobile-hiring@rajsecurity.in',
                'priority' => 2,
                'application_deadline' => '2024-02-15',
            ],
            [
                'title' => 'Marketing Manager - Digital',
                'department' => 'Marketing',
                'location' => 'Mumbai',
                'type' => 'full-time',
                'experience_level' => '4-6 years',
                'description' => 'Lead digital marketing initiatives to drive brand awareness and lead generation in the security industry. Manage campaigns across multiple channels.',
                'requirements' => ['Digital Marketing', 'Google Ads', 'SEO/SEM', 'Content Marketing', 'Analytics'],
                'salary_range' => '₹10-15 LPA',
                'status' => 'inactive',
                'contact_email' => 'marketing-hiring@rajsecurity.in',
                'priority' => 1,
                'application_deadline' => '2024-01-30',
            ],
        ];

        foreach ($jobOpenings as $job) {
            CompanyJobOpening::create($job);
        }
    }
}
