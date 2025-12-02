import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import Checkbox from '@/Components/Checkbox';
import Notification from '@/Components/Notification';

export default function CreateClient({ auth, companies = [], designations = [], agencies = [], taxTypes = {}, taxStatuses = {} }) {
    const [showPassword, setShowPassword] = useState(false);
    const [notification, setNotification] = useState({ show: false, message: '', type: 'success' });

    const { data, setData, post, processing, errors } = useForm({
        // Basic client information
        company_id: '',
        name: '',
        email: '',
        name_of_client: '',
        to_title: '',
        site_name: '',
        address: '',
        dob: '',
        date_of_anniversary: '',
        contact_no_1: '',
        contact_no_2: '',
        site_supervisor_contact: '',
        site_admin_contact: '',
        site_manager_contact: '',
        gst_no: '',
        tds_percentage: '',
        pan_no: '',
        primary_email_1: '',
        primary_email_2: '',
        additional_charges: '',
        additional_charges_comment: '',
        password: '',
        status: 'active',
        
        // Notification preferences
        sms_reports: false,
        sms_attendance: false,
        sms_bill: false,
        email_reports: false,
        email_attendance: false,
        email_bill: false,
        email_bill_reminder: false,
        email_payment_receipt: false,
        
        // Dynamic arrays
        contacts: [{ name: '', designation_id: '', contact_no: '', email: '' }],
        taxes: [{ tax_type: 'gst', percentage: '', status: 'active' }],
    });

    const addContact = () => {
        setData('contacts', [...data.contacts, { name: '', designation_id: '', contact_no: '', email: '' }]);
    };

    const removeContact = (index) => {
        if (data.contacts.length > 1) {
            const newContacts = data.contacts.filter((_, i) => i !== index);
            setData('contacts', newContacts);
        }
    };

    const updateContact = (index, field, value) => {
        const newContacts = [...data.contacts];
        newContacts[index][field] = value;
        setData('contacts', newContacts);
    };

    const addTax = () => {
        setData('taxes', [...data.taxes, { tax_type: 'gst', percentage: '', status: 'active' }]);
    };

    const removeTax = (index) => {
        if (data.taxes.length > 1) {
            const newTaxes = data.taxes.filter((_, i) => i !== index);
            setData('taxes', newTaxes);
        }
    };

    const updateTax = (index, field, value) => {
        const newTaxes = [...data.taxes];
        newTaxes[index][field] = value;
        setData('taxes', newTaxes);
    };

    const validateForm = () => {
        const newErrors = {};
        
        // Required field validation
        if (!data.name_of_client.trim()) {
            newErrors.name_of_client = 'Client name is required';
        }
        if (!data.name.trim()) {
            newErrors.name = 'Full name is required';
        }
        if (!data.email.trim()) {
            newErrors.email = 'Email is required';
        } else if (!/\S+@\S+\.\S+/.test(data.email)) {
            newErrors.email = 'Please enter a valid email address';
        }
        if (!data.password.trim()) {
            newErrors.password = 'Password is required';
        } else if (data.password.length < 6) {
            newErrors.password = 'Password must be at least 6 characters';
        }
        
        return newErrors;
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        // Client-side validation
        const validationErrors = validateForm();
        if (Object.keys(validationErrors).length > 0) {
            setNotification({
                show: true,
                message: 'Please fill in all required fields correctly.',
                type: 'warning'
            });
            return;
        }
        
        post(route('client.store-custom'), {
            onSuccess: () => {
                setNotification({
                    show: true,
                    message: 'Client created successfully! Redirecting to client list...',
                    type: 'success'
                });
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
                setNotification({
                    show: true,
                    message: 'Please check the form for errors and try again.',
                    type: 'error'
                });
            }
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Add New Client</h2>}
        >
            <Head title="Add Client" />
            
            <Notification
                message={notification.message}
                type={notification.type}
                show={notification.show}
                onClose={() => setNotification({ ...notification, show: false })}
            />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <form onSubmit={handleSubmit} className="space-y-8">
                                
                                {/* Basic Information Section */}
                                <div className="border-b border-gray-200 pb-6">
                                    <h3 className="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        
                                        <div>
                                            <InputLabel htmlFor="company_id" value="Company" />
                                            <select 
                                                id="company_id"
                                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                value={data.company_id}
                                                onChange={(e) => setData('company_id', e.target.value)}
                                            >
                                                <option value="">Select Company</option>
                                                {companies.map(company => (
                                                    <option key={company.id} value={company.id}>{company.name}</option>
                                                ))}
                                            </select>
                                            <InputError message={errors.company_id} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="name_of_client" value="Client Name *" />
                                            <TextInput
                                                id="name_of_client"
                                                type="text"
                                                className="mt-1 block w-full"
                                                value={data.name_of_client}
                                                onChange={(e) => setData('name_of_client', e.target.value)}
                                                required
                                            />
                                            <InputError message={errors.name_of_client} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="name" value="Full Name *" />
                                            <TextInput
                                                id="name"
                                                type="text"
                                                className="mt-1 block w-full"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                required
                                            />
                                            <InputError message={errors.name} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="email" value="Login Email *" />
                                            <TextInput
                                                id="email"
                                                type="email"
                                                className="mt-1 block w-full"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                required
                                            />
                                            <InputError message={errors.email} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="to_title" value="Title" />
                                            <select 
                                                id="to_title"
                                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                value={data.to_title}
                                                onChange={(e) => setData('to_title', e.target.value)}
                                            >
                                                <option value="">Select Title</option>
                                                <option value="Mr.">Mr.</option>
                                                <option value="Ms.">Ms.</option>
                                                <option value="Mrs.">Mrs.</option>
                                                <option value="Dr.">Dr.</option>
                                                <option value="Prof.">Prof.</option>
                                            </select>
                                            <InputError message={errors.to_title} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="site_name" value="Site Name" />
                                            <TextInput
                                                id="site_name"
                                                type="text"
                                                className="mt-1 block w-full"
                                                value={data.site_name}
                                                onChange={(e) => setData('site_name', e.target.value)}
                                            />
                                            <InputError message={errors.site_name} className="mt-2" />
                                        </div>

                                        <div className="md:col-span-2">
                                            <InputLabel htmlFor="address" value="Address" />
                                            <textarea
                                                id="address"
                                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                rows="3"
                                                value={data.address}
                                                onChange={(e) => setData('address', e.target.value)}
                                            />
                                            <InputError message={errors.address} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="contact_no_1" value="Primary Contact" />
                                            <TextInput
                                                id="contact_no_1"
                                                type="text"
                                                className="mt-1 block w-full"
                                                value={data.contact_no_1}
                                                onChange={(e) => setData('contact_no_1', e.target.value)}
                                            />
                                            <InputError message={errors.contact_no_1} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="contact_no_2" value="Secondary Contact" />
                                            <TextInput
                                                id="contact_no_2"
                                                type="text"
                                                className="mt-1 block w-full"
                                                value={data.contact_no_2}
                                                onChange={(e) => setData('contact_no_2', e.target.value)}
                                            />
                                            <InputError message={errors.contact_no_2} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="primary_email_1" value="Primary Email" />
                                            <TextInput
                                                id="primary_email_1"
                                                type="email"
                                                className="mt-1 block w-full"
                                                value={data.primary_email_1}
                                                onChange={(e) => setData('primary_email_1', e.target.value)}
                                            />
                                            <InputError message={errors.primary_email_1} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="primary_email_2" value="Secondary Email" />
                                            <TextInput
                                                id="primary_email_2"
                                                type="email"
                                                className="mt-1 block w-full"
                                                value={data.primary_email_2}
                                                onChange={(e) => setData('primary_email_2', e.target.value)}
                                            />
                                            <InputError message={errors.primary_email_2} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="dob" value="Date of Birth" />
                                            <TextInput
                                                id="dob"
                                                type="date"
                                                className="mt-1 block w-full"
                                                value={data.dob}
                                                onChange={(e) => setData('dob', e.target.value)}
                                            />
                                            <InputError message={errors.dob} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="date_of_anniversary" value="Anniversary Date" />
                                            <TextInput
                                                id="date_of_anniversary"
                                                type="date"
                                                className="mt-1 block w-full"
                                                value={data.date_of_anniversary}
                                                onChange={(e) => setData('date_of_anniversary', e.target.value)}
                                            />
                                            <InputError message={errors.date_of_anniversary} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="gst_no" value="GST Number" />
                                            <TextInput
                                                id="gst_no"
                                                type="text"
                                                className="mt-1 block w-full"
                                                value={data.gst_no}
                                                onChange={(e) => setData('gst_no', e.target.value)}
                                            />
                                            <InputError message={errors.gst_no} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="pan_no" value="PAN Number" />
                                            <TextInput
                                                id="pan_no"
                                                type="text"
                                                className="mt-1 block w-full"
                                                value={data.pan_no}
                                                onChange={(e) => setData('pan_no', e.target.value)}
                                            />
                                            <InputError message={errors.pan_no} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="tds_percentage" value="TDS Percentage (%)" />
                                            <TextInput
                                                id="tds_percentage"
                                                type="number"
                                                step="0.01"
                                                className="mt-1 block w-full"
                                                value={data.tds_percentage}
                                                onChange={(e) => setData('tds_percentage', e.target.value)}
                                            />
                                            <InputError message={errors.tds_percentage} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="additional_charges" value="Additional Charges" />
                                            <TextInput
                                                id="additional_charges"
                                                type="number"
                                                step="0.01"
                                                className="mt-1 block w-full"
                                                value={data.additional_charges}
                                                onChange={(e) => setData('additional_charges', e.target.value)}
                                            />
                                            <InputError message={errors.additional_charges} className="mt-2" />
                                        </div>

                                        <div className="md:col-span-2">
                                            <InputLabel htmlFor="additional_charges_comment" value="Additional Charges Comment" />
                                            <textarea
                                                id="additional_charges_comment"
                                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                rows="3"
                                                value={data.additional_charges_comment}
                                                onChange={(e) => setData('additional_charges_comment', e.target.value)}
                                            />
                                            <InputError message={errors.additional_charges_comment} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="password" value="Password *" />
                                            <div className="relative">
                                                <TextInput
                                                    id="password"
                                                    type={showPassword ? "text" : "password"}
                                                    className="mt-1 block w-full pr-10"
                                                    value={data.password}
                                                    onChange={(e) => setData('password', e.target.value)}
                                                    required
                                                />
                                                <button
                                                    type="button"
                                                    className="absolute inset-y-0 right-0 pr-3 flex items-center text-sm text-gray-600 hover:text-gray-800"
                                                    onClick={() => setShowPassword(!showPassword)}
                                                >
                                                    {showPassword ? '🙈' : '👁️'}
                                                </button>
                                            </div>
                                            <InputError message={errors.password} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="status" value="Status" />
                                            <select 
                                                id="status"
                                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                value={data.status}
                                                onChange={(e) => setData('status', e.target.value)}
                                            >
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                            <InputError message={errors.status} className="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                {/* Contacts Section */}
                                <div className="border-b border-gray-200 pb-6">
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-semibold text-gray-800">Client Contacts</h3>
                                        <button
                                            type="button"
                                            onClick={addContact}
                                            className="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors"
                                        >
                                            + Add Contact
                                        </button>
                                    </div>
                                    
                                    {data.contacts.map((contact, index) => (
                                        <div key={index} className="border border-gray-200 rounded-lg p-4 mb-4 relative">
                                            {data.contacts.length > 1 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeContact(index)}
                                                    className="absolute top-2 right-2 text-red-500 hover:text-red-700 text-xl"
                                                >
                                                    ×
                                                </button>
                                            )}
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <InputLabel value={`Contact ${index + 1} Name`} />
                                                    <TextInput
                                                        type="text"
                                                        className="mt-1 block w-full"
                                                        value={contact.name}
                                                        onChange={(e) => updateContact(index, 'name', e.target.value)}
                                                    />
                                                    <InputError message={errors[`contacts.${index}.name`]} className="mt-2" />
                                                </div>
                                                <div>
                                                    <InputLabel value="Designation" />
                                                    <select 
                                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                        value={contact.designation_id}
                                                        onChange={(e) => updateContact(index, 'designation_id', e.target.value)}
                                                    >
                                                        <option value="">Select Designation</option>
                                                        {designations.map(designation => (
                                                            <option key={designation.id} value={designation.id}>{designation.name}</option>
                                                        ))}
                                                    </select>
                                                    <InputError message={errors[`contacts.${index}.designation_id`]} className="mt-2" />
                                                </div>
                                                <div>
                                                    <InputLabel value="Contact Number" />
                                                    <TextInput
                                                        type="text"
                                                        className="mt-1 block w-full"
                                                        value={contact.contact_no}
                                                        onChange={(e) => updateContact(index, 'contact_no', e.target.value)}
                                                    />
                                                    <InputError message={errors[`contacts.${index}.contact_no`]} className="mt-2" />
                                                </div>
                                                <div>
                                                    <InputLabel value="Email" />
                                                    <TextInput
                                                        type="email"
                                                        className="mt-1 block w-full"
                                                        value={contact.email}
                                                        onChange={(e) => updateContact(index, 'email', e.target.value)}
                                                    />
                                                    <InputError message={errors[`contacts.${index}.email`]} className="mt-2" />
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* Tax Details Section */}
                                <div className="border-b border-gray-200 pb-6">
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-semibold text-gray-800">Tax Details</h3>
                                        <button
                                            type="button"
                                            onClick={addTax}
                                            className="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition-colors"
                                        >
                                            + Add Tax
                                        </button>
                                    </div>
                                    
                                    {data.taxes.map((tax, index) => (
                                        <div key={index} className="border border-gray-200 rounded-lg p-4 mb-4 relative">
                                            {data.taxes.length > 1 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeTax(index)}
                                                    className="absolute top-2 right-2 text-red-500 hover:text-red-700 text-xl"
                                                >
                                                    ×
                                                </button>
                                            )}
                                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <InputLabel value="Tax Type" />
                                                    <select 
                                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                        value={tax.tax_type}
                                                        onChange={(e) => updateTax(index, 'tax_type', e.target.value)}
                                                    >
                                                        <option value="gst">GST</option>
                                                        <option value="vat">VAT</option>
                                                        <option value="tds">TDS</option>
                                                        <option value="service_tax">Service Tax</option>
                                                        <option value="cess">Cess</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                    <InputError message={errors[`taxes.${index}.tax_type`]} className="mt-2" />
                                                </div>
                                                <div>
                                                    <InputLabel value="Percentage (%)" />
                                                    <TextInput
                                                        type="number"
                                                        step="0.01"
                                                        className="mt-1 block w-full"
                                                        value={tax.percentage}
                                                        onChange={(e) => updateTax(index, 'percentage', e.target.value)}
                                                    />
                                                    <InputError message={errors[`taxes.${index}.percentage`]} className="mt-2" />
                                                </div>
                                                <div>
                                                    <InputLabel value="Status" />
                                                    <select 
                                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                        value={tax.status}
                                                        onChange={(e) => updateTax(index, 'status', e.target.value)}
                                                    >
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                    </select>
                                                    <InputError message={errors[`taxes.${index}.status`]} className="mt-2" />
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* Notification Preferences */}
                                <div className="border-b border-gray-200 pb-6">
                                    <h3 className="text-lg font-semibold text-gray-800 mb-4">Notification Preferences</h3>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 className="font-medium text-gray-700 mb-3">SMS Notifications</h4>
                                            <div className="space-y-2">
                                                <label className="flex items-center">
                                                    <Checkbox
                                                        checked={data.sms_reports}
                                                        onChange={(e) => setData('sms_reports', e.target.checked)}
                                                    />
                                                    <span className="ml-2 text-gray-700">Reports</span>
                                                </label>
                                                <label className="flex items-center">
                                                    <Checkbox
                                                        checked={data.sms_attendance}
                                                        onChange={(e) => setData('sms_attendance', e.target.checked)}
                                                    />
                                                    <span className="ml-2 text-gray-700">Attendance</span>
                                                </label>
                                                <label className="flex items-center">
                                                    <Checkbox
                                                        checked={data.sms_bill}
                                                        onChange={(e) => setData('sms_bill', e.target.checked)}
                                                    />
                                                    <span className="ml-2 text-gray-700">Bills</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 className="font-medium text-gray-700 mb-3">Email Notifications</h4>
                                            <div className="space-y-2">
                                                <label className="flex items-center">
                                                    <Checkbox
                                                        checked={data.email_reports}
                                                        onChange={(e) => setData('email_reports', e.target.checked)}
                                                    />
                                                    <span className="ml-2 text-gray-700">Reports</span>
                                                </label>
                                                <label className="flex items-center">
                                                    <Checkbox
                                                        checked={data.email_attendance}
                                                        onChange={(e) => setData('email_attendance', e.target.checked)}
                                                    />
                                                    <span className="ml-2 text-gray-700">Attendance</span>
                                                </label>
                                                <label className="flex items-center">
                                                    <Checkbox
                                                        checked={data.email_bill}
                                                        onChange={(e) => setData('email_bill', e.target.checked)}
                                                    />
                                                    <span className="ml-2 text-gray-700">Bills</span>
                                                </label>
                                                <label className="flex items-center">
                                                    <Checkbox
                                                        checked={data.email_bill_reminder}
                                                        onChange={(e) => setData('email_bill_reminder', e.target.checked)}
                                                    />
                                                    <span className="ml-2 text-gray-700">Bill Reminders</span>
                                                </label>
                                                <label className="flex items-center">
                                                    <Checkbox
                                                        checked={data.email_payment_receipt}
                                                        onChange={(e) => setData('email_payment_receipt', e.target.checked)}
                                                    />
                                                    <span className="ml-2 text-gray-700">Payment Receipts</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Submit Button */}
                                <div className="flex justify-end">
                                    <PrimaryButton disabled={processing} className="px-6 py-2">
                                        {processing ? 'Creating...' : 'Create Client'}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
