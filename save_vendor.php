<?php
header('Content-Type: application/json');
require "config.php";

// -------------------------------
// FILE UPLOAD HELPERS
// -------------------------------
function uploadSingleFile($fieldName, $uploadDir = "uploads/") {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = basename($_FILES[$fieldName]['name']);
    $filename = time() . "_" . preg_replace('/\s+/', '_', $originalName);
    $targetPath = rtrim($uploadDir, "/") . "/" . $filename;

    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        return $filename;       // store relative name in DB
    }

    return null;
}

function uploadMultipleFilesArray($fieldName, $uploadDir = "uploads/") {
    if (!isset($_FILES[$fieldName])) {
        return null;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $saved = [];

    foreach ($_FILES[$fieldName]['tmp_name'] as $index => $tmpPath) {
        if ($_FILES[$fieldName]['error'][$index] === UPLOAD_ERR_OK) {
            $originalName = basename($_FILES[$fieldName]['name'][$index]);
            $filename = time() . "_" . $index . "_" . preg_replace('/\s+/', '_', $originalName);
            $targetPath = rtrim($uploadDir, "/") . "/" . $filename;

            if (move_uploaded_file($tmpPath, $targetPath)) {
                $saved[] = $filename;
            }
        }
    }

    return $saved ? json_encode($saved) : null;
}

// -------------------------------
// READ FORM DATA
// -------------------------------
$business_name        = $_POST['business_name']       ?? null;
$trading_name         = $_POST['trading_name']        ?? null;
$registration_number  = $_POST['registration_number'] ?? null;
$tin_vat              = $_POST['tin_vat']             ?? null;

$entity_type          = $_POST['entity_type']         ?? null;

$registered_address   = $_POST['registered_address']  ?? null;
$contact_person       = $_POST['contact_person']      ?? null;
$contact_number       = $_POST['contact_number']      ?? null;
$email                = $_POST['email']               ?? null;
$social_links         = $_POST['social_links']        ?? null;

$username             = $_POST['username']            ?? null;
$business_categories  = $_POST['business_categories'] ?? null;
$business_description = $_POST['business_description']?? null;

// file uploads
$logo_path    = uploadSingleFile("business_logo");
$samples_path = uploadMultipleFilesArray("sample_images");

// subscription plans (checkbox array)
$plans = $_POST['plans'] ?? [];
$plan_basic_annual     = in_array('basic_annual', $plans)     ? 1 : 0;
$plan_featured_weekly  = in_array('featured_weekly', $plans)  ? 1 : 0;
$plan_featured_monthly = in_array('featured_monthly', $plans) ? 1 : 0;
$plan_banner_weekly    = in_array('banner_weekly', $plans)    ? 1 : 0;
$plan_banner_monthly   = in_array('banner_monthly', $plans)   ? 1 : 0;

// declaration
$vendor_name     = $_POST['vendor_name'] ?? null;
$signature_path  = uploadSingleFile("signature");
$date            = $_POST['date']        ?? null;

// (Optional) basic required validation on backend as well
if (empty($business_name)) {
    echo json_encode(['success' => false, 'message' => 'Business name is required']);
    exit;
}

// -------------------------------
// INSERT INTO DB
// -------------------------------
$sql = "INSERT INTO eventcore_vendors (
    business_name,
    trading_name,
    registration_number,
    tin_vat,
    entity_type,
    registered_address,
    contact_person,
    contact_number,
    email,
    social_links,
    username,
    business_categories,
    business_description,
    logo_path,
    samples_path,
    plan_basic_annual,
    plan_featured_weekly,
    plan_featured_monthly,
    plan_banner_weekly,
    plan_banner_monthly,
    vendor_name,
    signature_path,
    date
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed']);
    exit;
}

$stmt->bind_param(
    "sssssssssssssssiiiiisss",
    $business_name,
    $trading_name,
    $registration_number,
    $tin_vat,
    $entity_type,
    $registered_address,
    $contact_person,
    $contact_number,
    $email,
    $social_links,
    $username,
    $business_categories,
    $business_description,
    $logo_path,
    $samples_path,
    $plan_basic_annual,
    $plan_featured_weekly,
    $plan_featured_monthly,
    $plan_banner_weekly,
    $plan_banner_monthly,
    $vendor_name,
    $signature_path,
    $date
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Vendor registered successfully', 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
