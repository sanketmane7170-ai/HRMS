<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Document\Entities\DocumentType;

class DocumentTypeSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $types = [
            [
                'name' => 'Employement Certificate',
                'template' => '<meta charset="UTF-8" />
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
                <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700;1,900&amp;display=swap" rel="stylesheet" />
                <style type="text/css" media="screen">body{font-family:Roboto,sans-serif}img{max-width:100%}</style>
                <div class="wrapper" style="max-width:850px;display:block;margin:auto;border:1px solid #ddd"><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="padding:20px 50px"><tbody><tr><td><p>Date: [[today]]</p></td></tr><tr><td><p><strong>To</strong><br />The Manager<br />Emirates NBD<br />United Arab Emirates</p></td></tr><tr><td><p style="text-align:center"><strong><u>Subject: Employment Certificate</u></strong></p></td></tr><tr><td><p>This is to certify that the employee mentioned below is working with<strong>MOM Digital</strong>with corresponding details stipulated herein.</p></td></tr><tr><td><table width="100%" border="1" cellspacing="0" cellpadding="10" bgcolor="#ffffff"><tbody><tr><td colspan="3" style="text-align:center"><b>Employee Details</b></td></tr><tr><td>Name:</td><td>[[name]]</td></tr><tr><td>Company Department:</td><td>[[department]]</td></tr><tr><td>Company Designation:</td><td>

                <span style=" background-color: rgb(255, 255, 255)">[[designation]]</span>
                </td></tr><tr><td>Nationality:</td><td>

                <span style=" background-color: rgb(255, 255, 255)">[[nationality]]</span>
                </td></tr><tr><td>Passport Number:</td><td>

                <span style="color: rgb(72, 72, 72); font-family: CircularStd; font-size: 15px; background-color: rgb(255, 255, 255)">[[passport_number]]</span>
                </td></tr><tr><td>Date of Joining:</td><td>

                <span style="color: rgb(72, 72, 72); font-family: CircularStd; font-size: 15px; background-color: rgb(255, 255, 255)">[[joining_date]]</span>
                </td></tr><tr><td>Monthly Gross Salary:</td><td>[[currency_symbol]] [[salary]]</td></tr><tr><td>UAE Dirhams:</td><td>

                <span style=" background-color: rgb(255, 255, 255)">[[currency]] [[salary_in_words]]</span>
                </td></tr></tbody></table></td></tr><tr><td><p>This certificate is issued at the request of the employee and does not constitute any financial liability/guarantee on our part. The company shall not be responsible for any consequences which may arise out of the issuance of this document.</p></td></tr><tr><td><p>Thank you.</p></td></tr><tr><td><p><strong>For MOM Digital</strong></p></td></tr><tr><td><p>Isha Bhatnagar<br />HR Advisor</p></td></tr></tbody></table></div>'
            ],
            [
                'name' => 'Salary Certificate',
                'template' => '<meta charset="UTF-8" />
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
                <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700;1,900&amp;display=swap" rel="stylesheet" />
                <style type="text/css" media="screen">body{font-family:Roboto,sans-serif}img{max-width:100%}</style>
                <div class="wrapper" style="max-width:850px;display:block;margin:auto;border:1px solid #ddd"><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="padding:20px 50px"><tbody><tr><td><p>Date: [[today]]</p></td></tr><tr><td><p><strong>To</strong><br />The Manager<br />Emirates NBD- Bank name will be given by the employee<br />United Arab Emirates</p></td></tr><tr><td><p style="text-align:center"><strong><u>Subject: Salary Certificate</u></strong></p></td></tr><tr><td><p>This is to certify that the employee mentioned below is working with<strong>MOM Digital LLC</strong>, with corresponding details stipulated herein.</p></td></tr><tr><td><table width="100%" border="1" cellspacing="0" cellpadding="10" bgcolor="#ffffff"><tbody><tr><td colspan="3" style="text-align:center"><b>Employee Details</b></td></tr><tr><td>Name:</td><td>[[first_name]] [[last_name]]</td></tr><tr><td>Sponsor:</td><td>[[department]]</td></tr><tr><td>Company Designation:</td><td>[[designation]]</td></tr><tr><td>Nationality:</td><td>[[nationality]]</td></tr><tr><td>Passport Number:</td><td>[[passport_number]]</td></tr><tr><td>Date of Joining:</td><td>[[joining_date]]</td></tr><tr><td>Monthly Gross Salary:</td><td>

                <span style=" background-color: rgb(255, 255, 255)">[[currency]] [[salary]]</span>
                </td></tr><tr><td>UAE Dirhams:</td><td>[[salary_in_words]]</td></tr></tbody></table></td></tr><tr><td><p>This certificate is issued at the request of the employee and does not constitute any financial liability/guarantee on our part. The company shall not be responsible for any consequences which may arise out of the issuance of this document.</p></td></tr><tr><td><p>Thank you.</p></td></tr><tr><td><p><strong>For MOM Digital LLC</strong></p></td></tr><tr><td><p>Isha Bhatnagar<br />HR Advisor</p></td></tr></tbody></table></div>'
            ],
            [
                'name' => 'Salary Transfer Certificate',
                'template' => '<meta charset="UTF-8" />
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
                <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700;1,900&amp;display=swap" rel="stylesheet" />
                <style type="text/css" media="screen">body{font-family:Roboto,sans-serif}img{max-width:100%}</style>
                <div class="wrapper" style="max-width:850px;display:block;margin:auto;border:1px solid #ddd"><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="padding:20px 50px"><tbody><tr><td><p>Date: [[today]]</p></td></tr><tr><td><p>To<br /><strong>The Manager<br />First Abu Dhabi Bank - Bank name will be given by the employee.<br />United Arab Emirates</strong></p></td></tr><tr><td><p><u><strong>Subject:</strong>Salary Transfer Letter</u></p></td></tr><tr><td><p>Dear Sir/Ma’am,</p></td></tr><tr><td><p>This is to confirm that&nbsp;

                <span style="color: rgb(72, 72, 72); font-family: CircularStd; font-size: 15px; background-color: rgb(255, 255, 255)">[[title]]</span>
                <strong>. [[name]]&nbsp;</strong>holder of&nbsp;<strong>[[nationality]]&nbsp;</strong>passport number&nbsp;<strong>[[passport_number]]&nbsp;</strong>is working with&nbsp;<strong>Mom Digital&nbsp;</strong>in the position of&nbsp;<strong>[[designation]]</strong>. He is employed with us since&nbsp;<strong>[[joining_date]]</strong>. His current monthly salary is&nbsp;<strong>AED [[salary]]&nbsp;</strong>inclusive of all.</p></td></tr><tr><td><p>We undertake that his monthly salary will be transferred by payroll to his bank account with IBAN&nbsp;<strong>[[bank_account]]&nbsp;</strong>and undertake not to transfer the salary to any other bank unless he produces a clearance letter from you.</p></td></tr><tr><td><p>In the event of the resignation/termination, we undertake to inform you accordingly and transfer his gratuity and final settlement of his dues with your bank.</p></td></tr><tr><td><p>This certificate is issued upon the employee’s request, and it does not constitute any financial guarantee or an undertaking from our organization.</p></td></tr><tr><td><p>Thank you.</p></td></tr><tr><td><p><strong>For MOM Digital</strong></p></td></tr><tr><td><p>Abhishek Krishna<br />CEO</p></td></tr></tbody></table></div>'
            ]
        ];
        foreach ($types as $type) {
            DocumentType::firstOrCreate(
                ['name' => $type['name']],
                ['template' => $type['template']]
            );
        }
    }
}
