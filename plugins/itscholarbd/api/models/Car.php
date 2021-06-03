<?php namespace ItScholarBd\Api\Models;
use Model;
use Carbon\Carbon;
/**
 * Model
 */
class Car extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'cars';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    const MODELLIST = [
                        1 => 'M/C Honda  CG 125CC',
                        2 => 'M/C  CG 125 CC HJ 1255',
                        3 => 'M/C Fashion Walton - 125CC'
                      ];

    const UNITLIST = [
                        1 => 'HQ Coy 33 Inf Div',
                        2 => 'HQ 44 Inf Bde',
                        3 => 'Sta HQ, Cumilla',
                        4 => 'Area HQ, Cumilla',
                        5 => '16 Inf PARA Bn',
                        6 => '3 BIR',
                        7 => '31BIR',
                        8 => '5 Sig Bn',
                        9 => '2 Engr Bn',
                        10 => '504 DOC,Cumilla',
                        11 => 'CMH,Cumilla',
                        12 => '31 FD Amb',
                        13 => 'SSD,Cumilla',
                        14 => 'Ordep,Cumilla',
                        15 => '33 FIU ,Cumilla',
                        16 => 'Mil Farm Cumilla',
                        17 => 'Det DGFI,Cumilla',
                        18 => 'DGSS,Cumilla',
                        19 => '118 Fd Wksp Coy EME',
                        20 => 'Det ASU, Cumilla',
                        21 => 'SHO, Cumilla',
                        22 => 'Static Sig Coy, Cumilla',
                        23 => 'GE(Army),Cumilla',
                        24 => 'BRU,Cumilla',
                        25 => '5 Sig Bn Wksp Sec EME',
                        26 => '2 Engr Bn Wksp Sec EME',
                        27 => 'Cumilla Cadet College',
                        28 => 'BNCC,Cumilla',
                        29 => 'Ispahani Public School and College',
                        30 => 'Feni Cadet College',
                        31 => 'GE(Army), Feni',
                      ];

    const ENTRY = 0;                  
    const CANNOTBERESOLVEDANDIN = 1; 
    const CANNOTBERESOLVEDANDOUT = 2; 
    const REPAIREDANDIN = 3;                  
    const REPAIREDANDOUT = 4; 
    
    const STATUSLIST = [
        self::ENTRY => 'Entry',
        self::REPAIREDANDIN => 'Repaired and In',
        self::REPAIREDANDOUT => 'Repaired and Out',
        self::CANNOTBERESOLVEDANDIN => 'Cannot be resolved and In',
        self::CANNOTBERESOLVEDANDOUT => 'Cannot be resolved and Out',
    ];

    public function spare(){
      return $this->hasOne('\ItScholarBd\Api\Models\Spare');
    } 
}
