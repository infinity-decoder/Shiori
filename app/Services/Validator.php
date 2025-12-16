<?php
class Validator
{
    /**
     * Validate student payload. Returns ['errors'=>[], 'data'=>clean array]
     * $isUpdate: when true, allow some fields to be empty/missing.
     */
    public static function validateStudent(array $input, array $files = [], bool $isUpdate = false): array
    {
        $errors = [];
        $data = [];

        // helper
        $get = fn($k) => isset($input[$k]) ? trim((string)$input[$k]) : '';

        // Required fields (for create) - updated to include DOB, Father Occupation, CNIC, Mobile, Address
        $required = [
            'roll_no', 'enrollment_no', 'class_id', 'section_id', 
            'student_name', 'dob', 'father_name', 'father_occupation', 
            'cnic', 'mobile', 'address', 'category_id', 'fcategory_id'
        ];
        foreach ($required as $r) {
            if (!$isUpdate && $get($r) === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $r)) . ' is required.';
            }
        }

        // Session (optional but validate format if given)
        $sessionRaw = $get('session');
        if ($sessionRaw !== '') {
            if (!preg_match('/^\d{4}-\d{4}$/', $sessionRaw)) {
                $errors[] = 'Session format must be YYYY-YYYY (e.g. 2025-2026).';
            } else {
                [$y1, $y2] = explode('-', $sessionRaw);
                if (((int)$y2) !== ((int)$y1 + 1)) {
                    $errors[] = 'Session end year must be start year + 1 (e.g. 2025-2026).';
                } else {
                    $data['session'] = $sessionRaw;
                }
            }
        } else {
            $data['session'] = null;
        }

        // basic sanitization
        $data['roll_no'] = $get('roll_no');
        $data['enrollment_no'] = $get('enrollment_no');
        $data['class_id'] = (int)$get('class_id');
        $data['section_id'] = (int)$get('section_id');
        $data['student_name'] = $get('student_name');
        $data['dob'] = $get('dob') ?: null;
        // validate date format (YYYY-MM-DD) if present
        if (!empty($data['dob'])) {
            $d = date_parse($data['dob']);
            if (!checkdate((int)$d['month'], (int)$d['day'], (int)$d['year'])) {
                $errors[] = 'Date of birth is invalid.';
            }
        }

        // B-Form: validate 13 digits if present
        $rawBForm = $get('b_form');
        $bFormDigits = preg_replace('/\D+/', '', $rawBForm);
        if ($bFormDigits !== '') {
            if (strlen($bFormDigits) !== 13) {
                $errors[] = 'B-Form must contain exactly 13 digits.';
            } else {
                $data['b_form'] = $bFormDigits;
            }
        } else {
            $data['b_form'] = null;
        }

        $data['father_name'] = $get('father_name');

        // CNIC: allow digits & dashes; normalize to digits-only for storage
        $rawCnic = $get('cnic');
        $cnicDigits = preg_replace('/\D+/', '', $rawCnic);
        if ($cnicDigits !== '') {
            if (strlen($cnicDigits) !== 13) {
                $errors[] = 'CNIC must contain 13 digits (with or without dashes).';
            } else {
                $data['cnic'] = $cnicDigits;
            }
        } else {
            $data['cnic'] = null;
        }

        // Mobile: digits only
        $rawMobile = $get('mobile');
        $mobileDigits = preg_replace('/\D+/', '', $rawMobile);
        if ($mobileDigits !== '') {
            if (strlen($mobileDigits) < 7 || strlen($mobileDigits) > 15) {
                $errors[] = 'Mobile number looks invalid.';
            } else {
                $data['mobile'] = $mobileDigits;
            }
        } else {
            $data['mobile'] = null;
        }

        $data['address'] = $get('address');
        $data['father_occupation'] = $get('father_occupation');
        // BPS: optional, numeric-ish (allow 1-3 digits)
        $bpsRaw = $get('bps');
        if ($bpsRaw !== '') {
            if (!preg_match('/^\d{1,3}$/', $bpsRaw)) {
                $errors[] = 'BPS must be a number (1-3 digits).';
            } else {
                $data['bps'] = (int)$bpsRaw;
            }
        } else {
            $data['bps'] = null;
        }

                // Religion
        $religion = $get('religion');
        if ($religion !== '') {
            if (mb_strlen($religion) > 100) $errors[] = 'Religion is too long.';
            $data['religion'] = $religion;
        } else {
            $data['religion'] = null;
        }

        // Caste
        $caste = $get('caste');
        if ($caste !== '') {
            if (mb_strlen($caste) > 100) $errors[] = 'Caste is too long.';
            $data['caste'] = $caste;
        } else {
            $data['caste'] = null;
        }

        // Domicile
        $domicile = $get('domicile');
        if ($domicile !== '') {
            if (mb_strlen($domicile) > 100) $errors[] = 'Domicile is too long.';
            $data['domicile'] = $domicile;
        } else {
            $data['domicile'] = null;
        }
        $data['category_id'] = (int)$get('category_id');
        $data['fcategory_id'] = (int)$get('fcategory_id');

        $email = $get('email');
        if ($email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email address.';
            } else {
                $data['email'] = $email;
            }
        } else {
            $data['email'] = null;
        }
        

        // photo validation (if present)
        if (!empty($files['photo']) && $files['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $files['photo'];
            $check = ImageService::validateUpload($file);
            if (!$check['ok']) {
                $errors[] = 'Photo: ' . $check['error'];
            }
        }

        return ['errors' => $errors, 'data' => $data];
    }
}
