<?php
/**
 * This file basically holds important info about some
 * Mtii values such as the LGA information and BusinesssPremises Prices
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;

/**
 * This file basically holds important info about some
 * Mtii values such as the LGA information and BusinesssPremises Prices
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class MtiiRelatedInformation
{
    private $_lga_and_codes = array(
        "AKWANGA_LOCAL_GOVERNMENT"                  => "01",
        "AWE_LOCAL_GOVERNMENT"                      => "02",
        "DOMA_LOCAL_GOVERNMENT"                     => "03",
        "KARU_LOCAL_GOVERNMENT"                     => "04",
        "KEANA_LOCAL_GOVERNMENT"                    => "05",
        "KEFFI_LOCAL_GOVERNMENT"                    => "06",
        "KOKONA_LOCAL_GOVERNMENT"                   => "07",
        "LAFIA_LOCAL_GOVERNMENT"                    => "08",
        "NASARAWA_LOCAL_GOVERNMENT"                 => "09",
        "NASARAWA_EGGON_LOCAL_GOVERNMENT"           => "10",
        "AGUNJI"                                    => "11",
        "TOTO_LOCAL_GOVERNMENT"                     => "12",
        "WAMBA_LOCAL_GOVERNMENT"                    => "13"
    );


    private $_wards_and_codes = array (
        "AKWANGA_LOCAL_GOVERNMENT"                  => "is_lga_parent",
        "AGYAGA"                  => "01",
        "ANCHO_BABA"              => "02",
        "GWANJE"                  => "03",
        "ANCHO_NIGHAAN"           => "04",
        "ANDAHA"                  => "05",
        "NUNKU"                   => "06",
        "GUDI"                    => "07",
        "MOROA"                   => "08",
        "AKWANGA_WEST"            => "09",
        "AKWANGA_EAST"            => "10",
        "NINGO_BOHAR"             => "11",

        "AWE_LOCAL_GOVERNMENT"                      => "is_lga_parent",
        "KANJE/ABUNI"             => "01",
        "MADAKI"                  => "02",
        "MAKWANGIJI"              => "03",
        "TUNGA"                   => "04",
        "RIBI"                    => "05",
        "AZARA"                   => "06",
        "GALADIMA"                => "07",
        "WUSE"                    => "08",
        "AKIRI"                   => "09",
        "JANGARU"                 => "10",

        "DOMA_LOCAL_GOVERNMENT"                     => "is_lga_parent",
        "ALAGYE"                 => "01",
        "RUKUBI"                 => "02",
        "AGBASHI"                => "03",
        "DOKA"                   => "04",
        "AKPANAJA"               => "05",
        "MADAKI"                 => "06",
        "UNG._SARKIN_DAWAKI"     => "07",
        "MADAUCHI"               => "08",
        "UNG._DANGALADIMA"       => "09",
        "SABON_GARI"             => "10",

        "KARU_LOCAL_GOVERNMENT"                     => "is_lga_parent",
        "ASO/KODAPE"             => "01",
        "AGADA/BAGAJI"           => "02",
        "KARSHI_I"               => "03",
        "KARSHI_II"              => "04",
        "KEFFIN SHANU/BETTI"     => "05",
        "TATTARA/KONDORO"        => "06",
        "GITATA"                 => "07",
        "GURKU/KABUSU"           => "08",
        "UKE"                    => "09",
        "PANDA/KARE"             => "10",
        "KARU"                   => "11",

        "KEANA_LOCAL_GOVERNMENT"                    => "is_lga_parent",
        "IWAGU"                   => "01",
        "AMIRI"                   => "02",
        "OBENE"                   => "03",
        "OKI"                     => "04",
        "KADARKO"                 => "05",
        "KWARA"                   => "06",
        "ALOSHI"                  => "07",
        "AGAZA"                   => "08",
        "GIZA_MADAKI"             => "09",
        "GIZA_GALADIMA"           => "10",

        "KEFFI_LOCAL_GOVERNMENT"                    => "is_lga_parent",
        "ANG._IYA_I"            => "01",
        "ANG._IYA_II"           => "02",
        "TUDUN_KOFA_TV"         => "03",
        "GANGAREN_TUDU "        => "04",
        "KEFFI_TOWN_EAST"       => "05",
        "YARA"                  => "06",
        "ANG._RIMI"             => "07",
        "SABON_GARI"            => "08",
        "JIGWADA"               => "09",
        "LIMAN_ABAJI"           => "10",

        "KOKONA_LOCAL_GOVERNMENT"                   => "is_lga_parent",
        "AGWADA"                => "01",
        "KOYA/KANA"             => "02",
        "BASSA"                 => "03",
        "KOKONA"                => "04",
        "KOFAR_GWARI"           => "05",
        "NINKORO "              => "06",
        "HADARI"                => "07",
        "DARI"                  => "08",
        "AMBA"                  => "09",
        "GARAKU"                => "10",
        "YALWA"                 => "11",

        "LAFIA_LOCAL_GOVERNMENT"                    => "is_lga_parent",
        "ADOGI"                 => "01",
        "AGYARAGU_TOFA"         => "02",
        "AKURBA"                => "03",
        "ARIKYA"                => "04",
        "ASSAKIO"               => "05",
        "ASHIGE"                => "06",
        "CIROMA"                => "07",
        "GAYAM"                 => "08",
        "KEFIN_WAMBAI"          => "09",
        "MAKAMA"                => "10",
        "SHABU_KWANDARE"        => "11",
        "WAKWA"                 => "12",
        "ZANWA"                 => "13",

        "NASARAWA_LOCAL_GOVERNMENT"                 => "is_lga_parent",
        "UDENIN_GIDA"         => "01",
        "AKUM"                => "02",
        "UDENIN_MAGAJI"       => "03",
        "LOKO"                => "04",
        "TUNGA/BAKONO"        => "05",
        "GUTO/AISA"           => "06",
        "NSW_NORTH"           => "07",
        "NSW_EAST"            => "08",
        "NSW_CENTRAL"         => "09",
        "NSW_MAIN_TOWN"       => "10",
        "ARA_I"               => "11",
        "ARA_II"              => "12",
        "LAMINGA"             => "13",
        "KONA/ONDA/APAWU"     => "14",
        "ODU"                 => "15",

        "NASARAW_EGGON_LOCAL_GOVERNMENT"            => "is_lga_parent",
        "N/EGGON"             => "01",
        "UBBE"                => "02",
        "IGGA/BURUM_BURUM"    => "03",
        "UMME"                => "04",
        "MADA_STATION"        => "05",
        "LIZZIN_KEFFI"        => "06",
        "LAMBAGA/ARIKPA"      => "07",
        "KAGBU_WANA"          => "08",
        "IKKA_WANGIBI"        => "09",
        "ENDE"                => "10",
        "WAKAMA"              => "11",
        "ALOCE/GINDA"         => "12",
        "ALOGANI"             => "13",
        "AGUNJI"              => "14",

        "AGUNJI"                                    => "is_lga_parent",
        "AGWATASHI"             => "01",
        "DADDARE/RIRI"          => "02",
        "OBI"                   => "03",
        "TUDUN_ADABU"           => "04",
        "DUDUGURU"              => "05",
        "GWADANYE"              => "06",
        "KYAKYALE"              => "07",
        "GIDAN_AUSA_I"          => "08",
        "GIDAN_AUSA_II"         => "09",
        "ADUDU"                 => "10",

        "TOTO_LOCAL_GOVERNMENT"                     => "is_lga_parent",
        "GWARGWADA"             => "01",
        "GADAGWA"               => "02",
        "BUGA_KARMO"            => "03",
        "UMAISHA"               => "04",
        "DAUSU"                 => "05",
        "KANYEHU"               => "06",
        "UGYA"                  => "07",
        "TOTO"                  => "08",
        "SHAFAN_ABAKWA"         => "09",
        "SHAFAN_ABAKWA"         => "10",
        "SHEGE"                 => "11",
        "KATAKPA"               => "12",

        "WAMBA_LOCAL_GOVERNMENT"                    => "is_lga_parent",
        "ARUM"             => "01",
        "MANGAR"           => "02",
        "GITTA"            => "03",
        "NAKERE"           => "04",
        "KONVAH"           => "05",
        "WAYO"             => "06",
        "WAMBA_WEST"       => "07",
        "WAMBA_EAST"       => "08",
        "KWARRA"           => "09",
        "JIMIYA"           => "10",
    );

    public function get_lga_code($lga)
    {
        $lga = str_replace(" ", "_", $lga);
        $all_lga = $this->_lga_and_codes;
        return isset($all_lga[$lga]) ? $all_lga[$lga] : null;
    }

    public function get_all_wards()
    {
        return $this->_wards_and_codes;
    }

    public function get_all_lga()
    {
        return $this->_lga_and_codes;
    }

    public function get_ward_code($ward)
    {
        $ward = str_replace(" ", "_", $ward);
        $all_ward = $this->_wards_and_codes;
        return isset($all_ward[$ward]) ? $all_ward[$ward] : null;
    }

    private $_business_prem_types_and_fees_new_registration = array (
        "MEDICALS AND HOSPITALITY BUSINESS"                     => 'is_group_description',
        "Private Hospitals"                                     => 30000,
        "Dispensary, Maternity Home, Nursing Clinics, Optical/ Pathology and Ex-Ray, Medical Lab" => 30000,
        "Health, Firms / Fitness Centre"                        => 20000,
        "Embalmment Centres"                                    => 14000,
        "Physiotherapy & Gyms"                                  => 14000,
        "Acupuncture Clinics (inpatients)"                      => 8000,
        "Nutritional and Food Supplement"                       => 15000,
        "Pharmacy Shops"                                        => 20000,
        "Patent/Proprietary Medicine Vendor license"            => 10000,
        "Hotels between 1-20 Rooms"                             => 40000,
        "Hotels 21-50 Rooms"                                    => 100000,
        "Hotels 51 Rooms and above"                             => 120000,
        "Five Star Hotels"                                      => 300000,

        "ENERGY, OIL AND GAS"                                   => 'is_group_description',
        "Petrol 1-2 pumps"                                      => 40000,
        "Petrol 1- 4 pumps"                                     => 50000,
        "Petrol 1-5 pumps"                                      => 100000,
        "Petrol 6 & above"                                      => 140000,
        "Surface Tank Kerosene Dealers"                         => 30000,
        "Gas Station"                                           => 500000,
        "Electricity Company"                                   => 5000000,
        "Electronic shops/ sellers"                             => 20000,

        "AUTOMOBILE AND BUILDING MATERIALS"         => 'is_group_description',
        "Sales of Aluminium profile"                => 20000,
        "Bench Saw Millers"                         => 30000,
        "Plank Seller/Dealer"                       => 15000,
        "Aluminium smelting Company"                => 500000,
        "Ceramic Company"                           => 5000000,
        "Steel smelting Company"                    => 5000000,
        "Building Material Dealer/Seller"           => 40000,
        "Cement Manufacturing Company"              => 12000000,
        "Cement dealer/Seller"                      => 40000,
        "Block Industry"                            => 20000,
        "Motor Spare parts Dealer"                  => 20000,
        "Motor Dealer/Seller"                       => 200000,
        "Motorcycle Dealer/Seller"                  => 100000,
        "Travelling Agency"                         => 10000,

        "ACADEMIC INSTITUTIONS"                      => 'is_group_description',
        "Private Nursery/primary Schools"           => 20000,
        "Private Secondary Schools"                 => 40000,
        "Vocational Centre / Secretariat Institute" => 20000,
        "Higher Institutions (Private)"             => 100000,

        "FINANCIAL INSTITUTIONS"                    => 'is_group_description',
        "Commercial Banks"                          => 200000,
        "Micro Finance Bank"                        => 80000,
        "Insurance Company"                         => 100000,

        "WHOLESALE AND RETAIL BUSINESS"             => 'is_group_description',
        "Bookshops/Stationeries stores"             => 14000,
        "Supermarkets"                              => 80000,
        "Distributorship"                           => 60000,
        "Small Retail shops"                        => 15000,
        "Clearing agents"                           => 20000,
        "Warehouse"                                 => 40000,

        "CAFÉ, EATERY AND FAST FOOD CENTERS"        => 'is_group_description',
        "Small Restaurant"                          => 24000,
        "Big Restaurant"                            => 48000,
        "Small Fast Food/Confectioneries"           => 10000,
        "Big Fast Food/Confectioneries"             => 24000,
        "Bakery"                                    => 20000,

        "GARMENTS AND FASHION DESIGN"               => 'is_group_description',
        "Tailor/Fashion Design"                     => 4000,
        "Interior Decorators"                       => 20000,
        "Launder / Dry cleaners"                    => 15000,
        "Boutique"                                  => 20000,

        "SOFT DRINK AND WATER PROCESSING"           => 'is_group_description',
        // "Soft drink/beverages bottling company"     => ,
        "Soft drink/beverages Depot"                => 40000,
        "Package Water Producer"                    => 20000,
        "Beer/Spirit Sales depot"                   => 80000,

        "AGRO ALLIED BUSINESS"                      => 'is_group_description',
        "Agro processing plants (Other Mills)"      => 20000,
        "Rental services"                           => 14000,
        "Agro Service/Chemical"                     => 10000,
        "Livestock Feed / Vet services"             => 20000,

        "BUSINESS CENTER AND SECRETARIAL SERVICES"  => 'is_group_description',
        "Pooling Centre/Game of Chance"             => 30000,
        "Printing Press (Large)"                    => 24000,
        "Printing Press (Medium)"                   => 20000,
        "Printing Press (Small)"                    => 10000,
        "Lawyers / Architect / Accountants"         => 70000,

        "WORKSHOPS AND GARAGE"                      => 'is_group_description',
        "Refrigerator/Radio/ Electronics Repairs"   => 6000,
        "Welding/Fabrication"                       => 10000,

        "CINEMATOGRAPHY"                            => 'is_group_description',
        "Cinema / Night Club (Big)"                 => 30000,
        "Cinema / Night Club (Medium)"              => 15000,
        "Cinema / Night Club (Small)"               => 10000,
        "Record stores, Video and Photography Club" => 10000,
        "Colour Labs / Processing centre"           => 20000,

        "COMMUNICATION AND ALLIED BUSINESS"         => 'is_group_description',
        "GSM Phone Accessories"                     => 4000,
        "GSM friendship centres (MTN, AIRTEL etc)"  => 30000,
        "Telecommunication Mask Location"           => 100000,
        "Telecommunication distribution"            => 200000,
        "Network service provider office"           => 500000,
        "Private Radio TV Station"                  => 200000,

        "CONSTRUCTION, EXTRACTION AND ALLIED BUSINESS"  => 'is_group_description',
        "Quarries"                                      => 500000,
        "Construction Company"                          => 5000000,
        "Borehole Company"                              => 300000,
        "Solid Mineral Mining Company"                  => 5000000
    );

    private $_business_prem_types_and_fees_renewal = array (
        "MEDICALS AND HOSPITALITY BUSINESS"                     => 'is_group_description',
        "Private Hospitals"                                     => 15000,
        "Dispensary, Maternity Home, Nursing Clinics, Optical/ Pathology and Ex-Ray, Medical Lab" => 15000,
        "Health, Firms / Fitness Centre"                        => 10000,
        "Embalmment Centres"                                    => 7000,
        "Physiotherapy & Gyms"                                  => 7000,
        "Acupuncture Clinics (inpatients)"                      => 4000,
        "Nutritional and Food Supplement"                       => 7500,
        "Pharmacy Shops"                                        => 10000,
        "Patent/Proprietary Medicine Vendor license"            => 5000,
        "Hotels between 1-20 Rooms"                             => 20000,
        "Hotels 21-50 Rooms"                                    => 50000,
        "Hotels 51 Rooms and above"                             => 60000,
        "Five Star Hotels"                                      => 150000,

        "ENERGY, OIL AND GAS"                                   => 'is_group_description',
        "Petrol 1-2 pumps"                                      => 20000,
        "Petrol 1- 4 pumps"                                     => 25000,
        "Petrol 1-5 pumps"                                      => 50000,
        "Petrol 6 & above"                                      => 70000,
        "Surface Tank Kerosene Dealers"                         => 15000,
        "Gas Station"                                           => 250000,
        "Electricity Company"                                   => 2500000,
        "Electronic shops/ sellers"                             => 10000,

        "AUTOMOBILE AND BUILDING MATERIALS"         => 'is_group_description',
        "Sales of Aluminium profile"                => 10000,
        "Bench Saw Millers"                         => 15000,
        "Plank Seller/Dealer"                       => 7500,
        "Aluminium smelting Company"                => 250000,
        "Ceramic Company"                           => 2500000,
        "Steel smelting Company"                    => 2500000,
        "Building Material Dealer/Seller"           => 20000,
        "Cement Manufacturing Company"              => 6000000,
        "Cement dealer/Seller"                      => 20000,
        "Block Industry"                            => 10000,
        "Motor Spare parts Dealer"                  => 10000,
        "Motor Dealer/Seller"                       => 100000,
        "Motorcycle Dealer/Seller"                  => 50000,
        "Travelling Agency"                         => 5000,

        "ACADEMIC INSTITUTIONS"                      => 'is_group_description',
        "Private Nursery/primary Schools"           => 10000,
        "Private Secondary Schools"                 => 20000,
        "Vocational Centre / Secretariat Institute" => 10000,
        "Higher Institutions (Private)"             => 50000,

        "FINANCIAL INSTITUTIONS"                    => 'is_group_description',
        "Commercial Banks"                          => 100000,
        "Micro Finance Bank"                        => 40000,
        "Insurance Company"                         => 50000,

        "WHOLESALE AND RETAIL BUSINESS"             => 'is_group_description',
        "Bookshops/Stationeries stores"             => 7000,
        "Supermarkets"                              => 40000,
        "Distributorship"                           => 30000,
        "Small Retail shops"                        => 7500,
        "Clearing agents"                           => 10000,
        "Warehouse"                                 => 20000,

        "CAFÉ, EATERY AND FAST FOOD CENTERS"        => 'is_group_description',
        "Small Restaurant"                          => 12000,
        "Big Restaurant"                            => 24000,
        "Small Fast Food/Confectioneries"           => 5000,
        "Big Fast Food/Confectioneries"             => 12000,
        "Bakery"                                    => 10000,

        "GARMENTS AND FASHION DESIGN"               => 'is_group_description',
        "Tailor/Fashion Design"                     => 2000,
        "Interior Decorators"                       => 10000,
        "Launder / Dry cleaners"                    => 7500,
        "Boutique"                                  => 10000,

        "SOFT DRINK AND WATER PROCESSING"           => 'is_group_description',
        // "Soft drink/beverages bottling company"     => ,
        "Soft drink/beverages Depot"                => 20000,
        "Package Water Producer"                    => 10000,
        "Beer/Spirit Sales depot"                   => 40000,

        "AGRO ALLIED BUSINESS"                      => 'is_group_description',
        "Agro processing plants (Other Mills)"      => 10000,
        "Rental services"                           => 7000,
        "Agro Service/Chemical"                     => 5000,
        "Livestock Feed / Vet services"             => 10000,

        "BUSINESS CENTER AND SECRETARIAL SERVICES"  => 'is_group_description',
        "Pooling Centre/Game of Chance"             => 15000,
        "Printing Press (Large)"                    => 12000,
        "Printing Press (Medium)"                   => 10000,
        "Printing Press (Small)"                    => 5000,
        "Lawyers / Architect / Accountants"         => 35000,

        "WORKSHOPS AND GARAGE"                      => 'is_group_description',
        "Refrigerator/Radio/ Electronics Repairs"   => 3000,
        "Welding/Fabrication"                       => 5000,

        "CINEMATOGRAPHY"                            => 'is_group_description',
        "Cinema / Night Club (Big)"                 => 15000,
        "Cinema / Night Club (Medium)"              => 7500,
        "Cinema / Night Club (Small)"               => 5000,
        "Record stores, Video and Photography Club" => 5000,
        "Colour Labs / Processing centre"           => 10000,

        "COMMUNICATION AND ALLIED BUSINESS"         => 'is_group_description',
        "GSM Phone Accessories"                     => 2000,
        "GSM friendship centres (MTN, AIRTEL etc)"  => 15000,
        "Telecommunication Mask Location"           => 50000,
        "Telecommunication distribution"            => 100000,
        "Network service provider office"           => 250000,
        "Private Radio TV Station"                  => 100000,

        "CONSTRUCTION, EXTRACTION AND ALLIED BUSINESS"  => 'is_group_description',
        "Quarries"                                      => 250000,
        "Construction Company"                          => 2500000,
        "Borehole Company"                              => 150000,
        "Solid Mineral Mining Company"                  => 2500000
    );

    public function get_all_biz_premises_amount($new_reg_or_renewal)
    {
        if ($new_reg_or_renewal == "mtii_new_registration") {
            return $this->_business_prem_types_and_fees_new_registration;
        } else if ($new_reg_or_renewal == "mtii_renewal") {
            return $this->_business_prem_types_and_fees_renewal;
        }
    }
}
?>