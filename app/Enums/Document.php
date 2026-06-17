<?php

namespace App\Enums;

enum Document: string
{
    case Passport = 'passport';
    case Visa = 'visa';
    case LaborCard = 'labor_card_no';
    case EmiratesID = 'emirates_id';
    case EidFront = 'eid_front';
    case EidBack = 'eid_back';
    case Insurance = 'insurance';
    case Education = 'education';
    case License = 'license';
    case Contract = 'contract';
    case Cv = 'cv';
    case HealthCard  = 'health_card';
    case OfferLetter = 'offer_letter';
    case EtisalatId = 'etisalat_id';
    case DuId = 'du_id';
    case Other = 'other';
    case PicCertification = 'pic_certification';
    case PassportPhoto = 'passport_photo';
    case IbanCertificate = 'iban_certificate';
    case VisaCancellation = 'visa_cancellation';
    case Degree = 'degree';
}
