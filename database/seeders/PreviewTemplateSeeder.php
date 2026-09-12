<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PreviewTemplate;
use App\Models\TemplateCategoryMapping;

class PreviewTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $websiteTemplates = [
            [
                'slug' => 'jewellery_site',
                'name' => 'Jewellery & Gold Luxury Showcase',
                'type' => 'website',
                'category_key' => 'jewellery',
                'description' => 'Luxury boutique layout with gold rate live marquee, bridal collection showcase, and VIP consultation booking.',
                'default_palette' => ['primary' => '#B45309', 'secondary' => '#1E293B', 'accent' => '#F59E0B', 'bg' => '#FFFBEB'],
                'sample_content' => [
                    'tagline' => 'Handcrafted Elegance & Purity Since Decades',
                    'features' => ['916 BIS Hallmarked Gold', 'Certified Natural Diamonds', 'Custom Bridal Jewellery', '0% Making Charge Offers'],
                ],
            ],
            [
                'slug' => 'real_estate_site',
                'name' => 'Real Estate & Properties',
                'type' => 'website',
                'category_key' => 'real_estate',
                'description' => 'Premium property portfolio layout with virtual tour inquiry, project amenities, and site visit booking.',
                'default_palette' => ['primary' => '#0F766E', 'secondary' => '#1E293B', 'accent' => '#14B8A6', 'bg' => '#F0FDFA'],
                'sample_content' => [
                    'tagline' => 'Discover Your Dream Luxury Living Spaces',
                    'features' => ['RERA Approved Projects', 'Prime Urban Locations', 'Flexible Payment Plans', 'Gated Community Amenities'],
                ],
            ],
            [
                'slug' => 'hospital_site',
                'name' => 'Hospital & Super Speciality Centre',
                'type' => 'website',
                'category_key' => 'hospital',
                'description' => 'Healthcare facility layout with 24/7 trauma emergency banner, specialist doctor directory, and OPD appointment scheduler.',
                'default_palette' => ['primary' => '#0284C7', 'secondary' => '#0F172A', 'accent' => '#38BDF8', 'bg' => '#F0F9FF'],
                'sample_content' => [
                    'tagline' => 'Compassionate Care, Advanced Medical Excellence',
                    'features' => ['24/7 Emergency & Trauma', 'Advanced Modular OT', 'NABH Accredited Healthcare', 'In-house Pharmacy & Diagnostics'],
                ],
            ],
            [
                'slug' => 'clinic_site',
                'name' => 'Clinic & Doctor Consultation',
                'type' => 'website',
                'category_key' => 'clinic',
                'description' => 'Specialized medical clinic layout focusing on doctor qualifications, clinical treatments, and one-tap appointment booking.',
                'default_palette' => ['primary' => '#0D9488', 'secondary' => '#134E4A', 'accent' => '#2DD4BF', 'bg' => '#F0FDFA'],
                'sample_content' => [
                    'tagline' => 'Personalized Medical Care For Your Entire Family',
                    'features' => ['Senior Specialist Doctors', 'Zero Waiting Time Bookings', 'Modern Diagnostic Equipment', 'Affordable Health Checkups'],
                ],
            ],
            [
                'slug' => 'hotel_site',
                'name' => 'Hotel & Luxury Resort',
                'type' => 'website',
                'category_key' => 'hotel',
                'description' => 'Hospitality layout with luxury suite galleries, dining highlights, banquet bookings, and instant tariff check.',
                'default_palette' => ['primary' => '#7C2D12', 'secondary' => '#1C1917', 'accent' => '#EA580C', 'bg' => '#FFF7ED'],
                'sample_content' => [
                    'tagline' => 'Experience Unrivaled Comfort & Royal Hospitality',
                    'features' => ['Luxury Air-Conditioned Suites', 'Multi-Cuisine Fine Dining', 'Grand Banquet & Conference Hall', 'Complimentary High-Speed Wi-Fi'],
                ],
            ],
            [
                'slug' => 'restaurant_site',
                'name' => 'Restaurant & Fine Dining',
                'type' => 'website',
                'category_key' => 'restaurant',
                'description' => 'Culinary layout with chef specialties, digital food menu preview, table reservations, and delivery partner links.',
                'default_palette' => ['primary' => '#DC2626', 'secondary' => '#18181B', 'accent' => '#F97316', 'bg' => '#FEF2F2'],
                'sample_content' => [
                    'tagline' => 'Authentic Flavors Prepared With Passion',
                    'features' => ['Fresh Organic Ingredients', 'Signature Chef Recipes', 'Hygienic Kitchen Standards', 'Family Dining & Party Space'],
                ],
            ],
            [
                'slug' => 'manufacturing_site',
                'name' => 'Manufacturing & Industrial Plant',
                'type' => 'website',
                'category_key' => 'manufacturing',
                'description' => 'B2B industrial layout highlighting production capacity, machinery specifications, ISO certifications, and RFQ submission.',
                'default_palette' => ['primary' => '#334155', 'secondary' => '#0F172A', 'accent' => '#F59E0B', 'bg' => '#F8FAFC'],
                'sample_content' => [
                    'tagline' => 'Precision Engineering & World-Class Industrial Manufacturing',
                    'features' => ['ISO 9001:2015 Certified', 'Large Scale OEM Production', 'Strict Quality Testing Lab', 'Global Export Compliance'],
                ],
            ],
            [
                'slug' => 'school_site',
                'name' => 'School & Academy',
                'type' => 'website',
                'category_key' => 'school',
                'description' => 'Educational institution layout with admissions notice board, extracurricular facility tour, and curriculum guides.',
                'default_palette' => ['primary' => '#1D4ED8', 'secondary' => '#1E1B4B', 'accent' => '#FBBF24', 'bg' => '#EFF6FF'],
                'sample_content' => [
                    'tagline' => 'Nurturing Future Leaders With Values & Excellence',
                    'features' => ['Holistic CBSE Curriculum', 'Smart Digital Classrooms', 'Sports & STEM Labs', 'Safe GPS-Enabled Transport'],
                ],
            ],
            [
                'slug' => 'college_site',
                'name' => 'College & University Campus',
                'type' => 'website',
                'category_key' => 'college',
                'description' => 'Higher education portal with course degrees, placement track records, campus life photos, and scholarship details.',
                'default_palette' => ['primary' => '#4338CA', 'secondary' => '#1E1B4B', 'accent' => '#6366F1', 'bg' => '#EEF2FF'],
                'sample_content' => [
                    'tagline' => 'Empowering Minds Through Higher Education & Research',
                    'features' => ['100% Placement Assistance', 'NAAC Accredited Grade A', 'Industry-Integrated Labs', 'Distinguished Doctorate Faculty'],
                ],
            ],
            [
                'slug' => 'automobile_site',
                'name' => 'Automobile Showroom & Service',
                'type' => 'website',
                'category_key' => 'automobile',
                'description' => 'Automotive dealership layout showcasing model line-up, test-drive booking, spare parts, and periodic service scheduler.',
                'default_palette' => ['primary' => '#B91C1C', 'secondary' => '#18181B', 'accent' => '#E11D48', 'bg' => '#FFF1F2'],
                'sample_content' => [
                    'tagline' => 'Drive Your Passion With Unmatched Performance & Reliability',
                    'features' => ['Authorized Dealership & Service', 'Genuine OEM Spare Parts', 'Instant Car Loan & Insurance', 'Express Periodic Maintenance'],
                ],
            ],
            [
                'slug' => 'interior_furniture_site',
                'name' => 'Interior Design & Luxury Furniture',
                'type' => 'website',
                'category_key' => 'interior_furniture',
                'description' => 'Aesthetic architectural layout with 3D design portfolio, modular kitchen options, and custom furniture quotation.',
                'default_palette' => ['primary' => '#78350F', 'secondary' => '#292524', 'accent' => '#D97706', 'bg' => '#FFFBEB'],
                'sample_content' => [
                    'tagline' => 'Transforming Spaces Into Breathtaking Living Experiences',
                    'features' => ['Custom Modular Kitchens', '10-Year Warranty Hardware', '3D Design Visualization', 'Turnkey Execution in 45 Days'],
                ],
            ],
            [
                'slug' => 'travel_tourism_site',
                'name' => 'Travel & Tourism Agency',
                'type' => 'website',
                'category_key' => 'travel_tourism',
                'description' => 'Holiday booking layout with international & domestic tour packages, itinerary schedules, and visa assistance CTAs.',
                'default_palette' => ['primary' => '#0284C7', 'secondary' => '#0C4A6E', 'accent' => '#06B6D4', 'bg' => '#F0FDF4'],
                'sample_content' => [
                    'tagline' => 'Unforgettable Journeys Crafted Just For You',
                    'features' => ['All-Inclusive Holiday Packages', 'Hassle-Free Visa Processing', '24/7 Dedicated Tour Manager', 'Best Price Guarantee'],
                ],
            ],
            [
                'slug' => 'logistics_site',
                'name' => 'Logistics, Cargo & Transport',
                'type' => 'website',
                'category_key' => 'logistics',
                'description' => 'Supply chain layout with freight quotation calculator, fleet tracking demo, and warehouse locations.',
                'default_palette' => ['primary' => '#D97706', 'secondary' => '#1E293B', 'accent' => '#2563EB', 'bg' => '#FFFBEB'],
                'sample_content' => [
                    'tagline' => 'Swift, Secure & Reliable Global Supply Chain Solutions',
                    'features' => ['Pan-India Express Fleet', 'Real-Time GPS Consignment Tracking', 'Secure Temperature-Controlled Storage', 'Full Truck Load (FTL) & Parcel'],
                ],
            ],
            [
                'slug' => 'professional_services_site',
                'name' => 'Professional & Corporate Services',
                'type' => 'website',
                'category_key' => 'professional_services',
                'description' => 'Corporate advisory layout for accounting, legal, consulting, and tax services with audit consultation inquiry.',
                'default_palette' => ['primary' => '#1E3A8A', 'secondary' => '#0F172A', 'accent' => '#3B82F6', 'bg' => '#F8FAFC'],
                'sample_content' => [
                    'tagline' => 'Strategic Advisory & Compliance Solutions For Growing Businesses',
                    'features' => ['GST & Income Tax Filing', 'Statutory Audit & Compliance', 'Company Incorporation', 'Business Strategy Advisory'],
                ],
            ],
            [
                'slug' => 'general_business_site',
                'name' => 'General Business Universal Showcase',
                'type' => 'website',
                'category_key' => 'general_business',
                'description' => 'Universal high-converting business showcase with hero section, service matrix, client feedback, and instant call-back form.',
                'default_palette' => ['primary' => '#1E3A5F', 'secondary' => '#0F172A', 'accent' => '#0EA5E9', 'bg' => '#F8FAFC'],
                'sample_content' => [
                    'tagline' => 'Trusted Local Solutions Delivered With Excellence & Integrity',
                    'features' => ['Certified Professional Team', 'Prompt Customer Support', 'Transparent Fair Pricing', 'Proven Customer Satisfaction'],
                ],
            ],
        ];

        $appTemplates = [
            [
                'slug' => 'ecommerce_app',
                'name' => 'E-Commerce Store Mobile App',
                'type' => 'app',
                'category_key' => 'ecommerce_app',
                'description' => 'Mobile commerce app UI with flash sale carousels, category grid, product cards, and cart drawer mockup.',
                'default_palette' => ['primary' => '#4F46E5', 'secondary' => '#1E1B4B', 'accent' => '#10B981', 'bg' => '#F8FAFC'],
                'sample_content' => [
                    'tagline' => 'Shop Trending Products at Unbeatable Prices',
                    'features' => ['One-Tap Checkout', 'Track Orders Live', 'Exclusive App Discounts', 'Multiple Payment Options'],
                ],
            ],
            [
                'slug' => 'service_booking_app',
                'name' => 'Service Booking Mobile App',
                'type' => 'app',
                'category_key' => 'service_booking_app',
                'description' => 'On-demand service booking UI with date/time slot picker, verified technician cards, and live status mockup.',
                'default_palette' => ['primary' => '#0284C7', 'secondary' => '#0F172A', 'accent' => '#F59E0B', 'bg' => '#F0F9FF'],
                'sample_content' => [
                    'tagline' => 'Book Trusted Local Experts in Under 60 Seconds',
                    'features' => ['Verified Professionals', 'Upfront Transparent Pricing', 'Real-Time Arrival Tracking', 'Customer Satisfaction Guarantee'],
                ],
            ],
            [
                'slug' => 'catalogue_app',
                'name' => 'Business Catalogue & B2B App',
                'type' => 'app',
                'category_key' => 'catalogue_app',
                'description' => 'Wholesale digital lookbook UI with high-res product galleries, stock availability tags, and WhatsApp quotation button.',
                'default_palette' => ['primary' => '#B45309', 'secondary' => '#1C1917', 'accent' => '#F59E0B', 'bg' => '#FFFBEB'],
                'sample_content' => [
                    'tagline' => 'Browse Our Complete Wholesale & Retail Catalogue',
                    'features' => ['Instant PDF Spec Downloads', 'Direct Factory Price Inquiries', 'Stock Availability Indicator', 'Quick WhatsApp Chat'],
                ],
            ],
            [
                'slug' => 'restaurant_app',
                'name' => 'Restaurant & Food Delivery App',
                'type' => 'app',
                'category_key' => 'restaurant_app',
                'description' => 'Food ordering app UI with interactive menu categories, add-to-cart badges, table reservation, and rider tracking mockup.',
                'default_palette' => ['primary' => '#EA580C', 'secondary' => '#18181B', 'accent' => '#FACC15', 'bg' => '#FFF7ED'],
                'sample_content' => [
                    'tagline' => 'Delicious Food Delivered Hot & Fresh To Your Door',
                    'features' => ['Live Kitchen Tracking', 'Customizable Food Add-ons', 'Table Booking In 2 Taps', 'Loyalty Rewards & Cashbacks'],
                ],
            ],
            [
                'slug' => 'real_estate_app',
                'name' => 'Real Estate Property Finder App',
                'type' => 'app',
                'category_key' => 'real_estate_app',
                'description' => 'Property exploration app UI with map search mockup, photo carousels, EMI calculator, and schedule site visit button.',
                'default_palette' => ['primary' => '#0D9488', 'secondary' => '#134E4A', 'accent' => '#14B8A6', 'bg' => '#F0FDFA'],
                'sample_content' => [
                    'tagline' => 'Find Verified Homes, Flats & Commercial Spaces',
                    'features' => ['360 Virtual Property Tours', 'Direct Builder Contact', 'Instant EMI Calculator', 'Saved Shortlists & Alerts'],
                ],
            ],
            [
                'slug' => 'hospital_clinic_app',
                'name' => 'Patient Care & Appointment App',
                'type' => 'app',
                'category_key' => 'hospital_clinic_app',
                'description' => 'Healthcare patient app UI with doctor slot booking, digital prescription records mockup, and live OPD queue token tracker.',
                'default_palette' => ['primary' => '#0284C7', 'secondary' => '#0F172A', 'accent' => '#10B981', 'bg' => '#F0F9FF'],
                'sample_content' => [
                    'tagline' => 'Book Doctor Appointments & Access Lab Reports Instantly',
                    'features' => ['Instant Doctor Consultation', 'Live OPD Token Status', 'Secure Health Records Archive', 'Medicine Reminder Notifications'],
                ],
            ],
            [
                'slug' => 'education_app',
                'name' => 'Student & Academy Portal App',
                'type' => 'app',
                'category_key' => 'education_app',
                'description' => 'E-learning app UI with video lectures mockup, attendance tracker, homework notice board, and teacher messaging.',
                'default_palette' => ['primary' => '#7C3AED', 'secondary' => '#1E1B4B', 'accent' => '#F59E0B', 'bg' => '#F5F3FF'],
                'sample_content' => [
                    'tagline' => 'Your Personalized Learning Companion On The Go',
                    'features' => ['Interactive Video Lessons', 'Live Class Schedule Alerts', 'Automated Quiz & Progress Card', 'Parent-Teacher Communication'],
                ],
            ],
            [
                'slug' => 'general_business_app',
                'name' => 'Universal Business Companion App',
                'type' => 'app',
                'category_key' => 'general_business_app',
                'description' => 'Universal brand app mockup with quick service catalog, one-tap calling, appointment requests, and review showcase.',
                'default_palette' => ['primary' => '#1E3A5F', 'secondary' => '#0F172A', 'accent' => '#0EA5E9', 'bg' => '#F8FAFC'],
                'sample_content' => [
                    'tagline' => 'Access Premium Business Services With a Single Tap',
                    'features' => ['One-Tap Direct Calling', 'Instant Service Request Form', 'Customer Reviews & Ratings', 'Location & Hours Map View'],
                ],
            ],
        ];

        // Seed all preview templates
        foreach (array_merge($websiteTemplates, $appTemplates) as $tpl) {
            PreviewTemplate::updateOrCreate(['slug' => $tpl['slug']], $tpl);
        }

        // Cache templates for lookup
        $tplLookup = PreviewTemplate::all()->keyBy('slug');

        // Seed Category Mappings
        $mappings = [
            ['pattern' => 'jewel%', 'site' => 'jewellery_site', 'app' => 'catalogue_app', 'prio' => 50],
            ['pattern' => 'gold%', 'site' => 'jewellery_site', 'app' => 'catalogue_app', 'prio' => 45],
            ['pattern' => 'hospital%', 'site' => 'hospital_site', 'app' => 'hospital_clinic_app', 'prio' => 50],
            ['pattern' => 'doctor%', 'site' => 'clinic_site', 'app' => 'hospital_clinic_app', 'prio' => 45],
            ['pattern' => 'clinic%', 'site' => 'clinic_site', 'app' => 'hospital_clinic_app', 'prio' => 45],
            ['pattern' => 'health%', 'site' => 'hospital_site', 'app' => 'hospital_clinic_app', 'prio' => 40],
            ['pattern' => 'dental%', 'site' => 'clinic_site', 'app' => 'hospital_clinic_app', 'prio' => 40],
            ['pattern' => 'real estate%', 'site' => 'real_estate_site', 'app' => 'real_estate_app', 'prio' => 50],
            ['pattern' => 'property%', 'site' => 'real_estate_site', 'app' => 'real_estate_app', 'prio' => 45],
            ['pattern' => 'builder%', 'site' => 'real_estate_site', 'app' => 'real_estate_app', 'prio' => 45],
            ['pattern' => 'hotel%', 'site' => 'hotel_site', 'app' => 'service_booking_app', 'prio' => 50],
            ['pattern' => 'resort%', 'site' => 'hotel_site', 'app' => 'service_booking_app', 'prio' => 45],
            ['pattern' => 'lodg%', 'site' => 'hotel_site', 'app' => 'service_booking_app', 'prio' => 40],
            ['pattern' => 'restaurant%', 'site' => 'restaurant_site', 'app' => 'restaurant_app', 'prio' => 50],
            ['pattern' => 'cafe%', 'site' => 'restaurant_site', 'app' => 'restaurant_app', 'prio' => 45],
            ['pattern' => 'bakery%', 'site' => 'restaurant_site', 'app' => 'restaurant_app', 'prio' => 40],
            ['pattern' => 'food%', 'site' => 'restaurant_site', 'app' => 'restaurant_app', 'prio' => 40],
            ['pattern' => 'manufactur%', 'site' => 'manufacturing_site', 'app' => 'catalogue_app', 'prio' => 50],
            ['pattern' => 'industry%', 'site' => 'manufacturing_site', 'app' => 'catalogue_app', 'prio' => 45],
            ['pattern' => 'factory%', 'site' => 'manufacturing_site', 'app' => 'catalogue_app', 'prio' => 45],
            ['pattern' => 'school%', 'site' => 'school_site', 'app' => 'education_app', 'prio' => 50],
            ['pattern' => 'academy%', 'site' => 'school_site', 'app' => 'education_app', 'prio' => 45],
            ['pattern' => 'college%', 'site' => 'college_site', 'app' => 'education_app', 'prio' => 50],
            ['pattern' => 'university%', 'site' => 'college_site', 'app' => 'education_app', 'prio' => 50],
            ['pattern' => 'automobile%', 'site' => 'automobile_site', 'app' => 'service_booking_app', 'prio' => 50],
            ['pattern' => 'car%', 'site' => 'automobile_site', 'app' => 'service_booking_app', 'prio' => 45],
            ['pattern' => 'bike%', 'site' => 'automobile_site', 'app' => 'service_booking_app', 'prio' => 45],
            ['pattern' => 'furniture%', 'site' => 'interior_furniture_site', 'app' => 'ecommerce_app', 'prio' => 50],
            ['pattern' => 'interior%', 'site' => 'interior_furniture_site', 'app' => 'catalogue_app', 'prio' => 50],
            ['pattern' => 'travel%', 'site' => 'travel_tourism_site', 'app' => 'service_booking_app', 'prio' => 50],
            ['pattern' => 'tour%', 'site' => 'travel_tourism_site', 'app' => 'service_booking_app', 'prio' => 45],
            ['pattern' => 'transport%', 'site' => 'logistics_site', 'app' => 'general_business_app', 'prio' => 50],
            ['pattern' => 'logistics%', 'site' => 'logistics_site', 'app' => 'general_business_app', 'prio' => 50],
            ['pattern' => 'cargo%', 'site' => 'logistics_site', 'app' => 'general_business_app', 'prio' => 45],
            ['pattern' => 'courier%', 'site' => 'logistics_site', 'app' => 'general_business_app', 'prio' => 45],
            ['pattern' => 'legal%', 'site' => 'professional_services_site', 'app' => 'general_business_app', 'prio' => 50],
            ['pattern' => 'advocate%', 'site' => 'professional_services_site', 'app' => 'general_business_app', 'prio' => 45],
            ['pattern' => 'consultant%', 'site' => 'professional_services_site', 'app' => 'general_business_app', 'prio' => 45],
            ['pattern' => 'accounting%', 'site' => 'professional_services_site', 'app' => 'general_business_app', 'prio' => 45],
            ['pattern' => 'general_business%', 'site' => 'general_business_site', 'app' => 'general_business_app', 'prio' => 1],
        ];

        foreach ($mappings as $m) {
            $siteTpl = $tplLookup->get($m['site']);
            $appTpl = $tplLookup->get($m['app']);

            if ($siteTpl && $appTpl) {
                TemplateCategoryMapping::updateOrCreate(
                    ['category_pattern' => $m['pattern']],
                    [
                        'website_template_id' => $siteTpl->id,
                        'app_template_id' => $appTpl->id,
                        'priority' => $m['prio'],
                    ]
                );
            }
        }
    }
}
