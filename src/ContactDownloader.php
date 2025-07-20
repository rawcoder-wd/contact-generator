<?php

namespace CardZen\ContactDownloader;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class ContactDownloader
{
    private $full_name;
    private $emails = [];
    private $phones = [];
    private $address = '';
    private $title = '';
    private $company = '';
    private $description = '';
    private $profile_image = '';
    private $social = [];

    /**
     * Set the full name for the VCF.
     *
     * @param string $full_name
     * @return $this
     */
    public function setFullName($full_name): self
    {
        $this->full_name = $full_name;
        return $this;
    }

    /**
     * Add an email address with a type to the VCF.
     *
     * @param string $email
     * @param string $type
     * @return $this
     */
    public function setEmail($email, $type): self
    {
        $this->emails[] = [
            'value' => $email,
            'type' => $type
        ];
        return $this;
    }

    /**
     * Add a phone number with a type to the VCF.
     *
     * @param string $phone
     * @param string $type
     * @return $this
     */
    public function setPhoneNumber($phone, $type): self
    {
        $this->phones[] = [
            'value' => $phone,
            'type' => $type
        ];
        return $this;
    }

    /**
     * Set the address for the VCF.
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address): self
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Set the title for the VCF.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the company for the VCF.
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Set the description (note) for the VCF.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the profile image for the VCF.
     *
     * @param string $profile_image
     * @return $this
     */
    public function setProfileImage($profile_image): self
    {
        $this->profile_image = $profile_image;
        return $this;
    }

    /**
     * Add a Facebook URL to the VCF.
     *
     * @param string $url
     * @return $this
     */
    public function setFacebook($url): self
    {
        $this->social['facebook'] = $url;
        return $this;
    }

    /**
     * Add a Twitter URL to the VCF.
     *
     * @param string $url
     * @return $this
     */
    public function setTwitter($url): self
    {
        $this->social['twitter'] = $url;
        return $this;
    }

    /**
     * Add a LinkedIn URL to the VCF.
     *
     * @param string $url
     * @return $this
     */
    public function setLinkedin($url): self
    {
        $this->social['linkedin'] = $url;
        return $this;
    }

    /**
     * Add a YouTube URL to the VCF.
     *
     * @param string $url
     * @return $this
     */
    public function setYoutube($url): self
    {
        $this->social['youtube'] = $url;
        return $this;
    }

    /**
     * Add an Instagram URL to the VCF.
     *
     * @param string $url
     * @return $this
     */
    public function setInstagram($url): self
    {
        $this->social['instagram'] = $url;
        return $this;
    }

    /**
     * Add a website URL to the VCF.
     *
     * @param string $url
     * @return $this
     */
    public function setWebsite($url): self
    {
        $this->social['website'] = $url;
        return $this;
    }

    /**
     * Add a Skype URL to the VCF.
     *
     * @param string $url
     * @return $this
     */
    public function setSkype($url): self
    {
        $this->social['skype'] = $url;
        return $this;
    }

    /**
     * Generate the VCF content.
     *
     * @return string
     */
    public function vcfContent(): string
    {
        $vcfContent = "BEGIN:VCARD\nVERSION:4.0";
        if (isset($this->full_name)) {
            $vcfContent .= "\nFN:" . $this->full_name;
            $vcfContent .= "\nN:" . $this->full_name;
        }
        if (isset($this->title)) {
            $vcfContent .= "\nTITLE:" . $this->title;
            $vcfContent .= "\nROLE:" . $this->title;
        }
        if (isset($this->company)) {
            $vcfContent .= "\nORG:" . $this->company;
        }
        if (isset($this->description)) {
            $vcfContent .= "\nNOTE:" . $this->description;
        }
        if (!empty($this->phones)) {
            foreach ($this->phones as $phone) {
                $vcfContent .= "\nTEL;TYPE=" . strtoupper($phone['type']) . ":" . Str::replace(' ', '', $phone['value']);
            }
        }
        if (!empty($this->emails)) {
            foreach ($this->emails as $email) {
                $vcfContent .= "\nEMAIL;TYPE=" . strtoupper($email['type']) . ":" . $email['value'];
            }
        }
        if (isset($this->address)) {
            $vcfContent .= "\nADR:;;" . $this->address . ";;;";
        }
        if (!empty($this->social)) {
            foreach ($this->social as $key => $social) {
                switch ($key) {
                    case 'whatsapp':
                        $vcfContent .= "\nURL;TYPE=WHATSAPP:https://wa.me/" . Str::remove(' ', $social);
                        break;
                    default:
                        $vcfContent .= "\nURL;TYPE=" . strtoupper($key) . ":" . $social;
                        break;
                }
            }
        }
        if (isset($this->profile_image) && File::exists($this->profile_image)) {
            $vcfContent .= "\nPHOTO;TYPE=PNG;ENCODING=b:" . base64_encode(File::get($this->profile_image));
        }
        $vcfContent .= "\nEND:VCARD";

        return $vcfContent;
    }

    /**
     * Generate the VCF content encoded in base64.
     *
     * @return string
     */
    public function toBase64(): string
    {
        return base64_encode($this->vcfContent());
    }

    /**
     * Generate a data URI for the VCF content.
     *
     * @return string
     */
    public function toUri(): string
    {
        return "data:text/x-vcard;charset=utf-8," . rawurlencode($this->vcfContent());
    }

    /**
     * Create a response to download the VCF file.
     *
     * @return \Illuminate\Http\Response
     */
    public function download()
    {
        $headers = [
            'Content-Type' => 'text/x-vcard',
            'Content-Disposition' => 'attachment; filename="' . $this->full_name . '.vcf"',
        ];

        return Response::make($this->vcfContent(), 200, $headers);
    }

    /**
     * Validate the provided email address.
     *
     * @param string $email
     * @return bool
     */
    public function validateEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate the provided phone number.
     *
     * @param string $phone
     * @return bool
     */
    public function validatePhoneNumber($phone): bool
    {
        return preg_match('/^\+?[0-9\s]+$/', $phone);
    }

    /**
     * Clear all the stored VCF data.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->full_name = '';
        $this->emails = [];
        $this->phones = [];
        $this->address = '';
        $this->title = '';
        $this->company = '';
        $this->description = '';
        $this->profile_image = '';
        $this->social = [];
        return $this;
    }

    /**
     * Set multiple attributes at once.
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            switch ($key) {
                case 'full_name':
                    $this->setFullName($value);
                    break;
                case 'email':
                    if (is_array($value)) {
                        foreach ($value as $email) {
                            $this->setEmail($email['value'], $email['type']);
                        }
                    }
                    break;
                case 'phone':
                    if (is_array($value)) {
                        foreach ($value as $phone) {
                            $this->setPhoneNumber($phone['value'], $phone['type']);
                        }
                    }
                    break;
                case 'address':
                    $this->setAddress($value);
                    break;
                case 'title':
                    $this->setTitle($value);
                    break;
                case 'company':
                    $this->setCompany($value);
                    break;
                case 'description':
                    $this->setDescription($value);
                    break;
                case 'profile_image':
                    $this->setProfileImage($value);
                    break;
                case 'social':
                    if (is_array($value)) {
                        foreach ($value as $platform => $url) {
                            $method = 'set' . ucfirst($platform);
                            if (method_exists($this, $method)) {
                                $this->$method($url);
                            }
                        }
                    }
                    break;
            }
        }
        return $this;
    }
}
