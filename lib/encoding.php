<?php
namespace APDL;

interface APDL_ENCODER {

    public function encode($data);

    public function decode($data);

    public function check($data);
}

class JSONEncoder implements APDL_ENCODER {

    public function encode($data) {
        return json_encode($data);
    }

    public function decode($data) {
        return json_decode($data);
    }

    public function check($data) {
        return (json_encode($data) != NULL);
    }
}

register_encoder("json", "\APDL\JSONEncoder");

class SB64Encoder implements APDL_ENCODER {

    public function encode($data) {
        return base64_encode(serialize($data));
    }

    public function decode($data) {
        return unserialize(base64_decode($data));
    }

    public function check($data) {
        $sdata = base64_decode($data);
        $usdata = unserialize($sdata);
        return ($usdata !== false || $sdata === 'b:0;');
    }
}

register_encoder("sb64", "\APDL\SB64Encoder");



log("Encoders Loaded", Log::L_INFO);