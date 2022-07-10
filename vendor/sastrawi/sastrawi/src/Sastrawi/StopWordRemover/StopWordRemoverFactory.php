<?php

namespace Sastrawi\StopWordRemover;

use Sastrawi\Dictionary\ArrayDictionary;

class StopWordRemoverFactory
{
    public function createStopWordRemover()
    {
        $stopWords = $this->getStopWords();

        $dictionary = new ArrayDictionary($stopWords);
        $stopWordRemover = new StopWordRemover($dictionary);

        return $stopWordRemover;
    }

    public function getStopWords()
    {
        return array(
            'yang', 'untuk', 'pada', 'ke', 'para', 'namun', 'menurut', 'antara', 'dia', 'dua',
            'ia', 'seperti', 'jika', 'jika', 'sehingga', 'kembali', 'dan', 'tidak', 'ini', 'karena',
            'kepada', 'oleh', 'saat', 'harus', 'sementara', 'setelah', 'belum', 'kami', 'sekitar',
            'bagi', 'serta', 'di', 'dari', 'telah', 'sebagai', 'masih', 'hal', 'ketika', 'adalah',
            'itu', 'dalam', 'bisa', 'bahwa', 'atau', 'hanya', 'kita', 'dengan', 'akan', 'juga',
            'ada', 'mereka', 'sudah', 'saya', 'terhadap', 'secara', 'agar', 'lain', 'anda',
            'begitu', 'mengapa', 'kenapa', 'yaitu', 'yakni', 'daripada', 'itulah', 'lagi', 'maka',
            'tentang', 'demi', 'dimana', 'kemana', 'pula', 'sambil', 'sebelum', 'sesudah', 'supaya',
            'guna', 'kah', 'pun', 'sampai', 'sedangkan', 'selagi', 'sementara', 'tetapi', 'apakah',
            'kecuali', 'sebab', 'selain', 'seolah', 'seraya', 'seterusnya', 'tanpa', 'agak', 'boleh',
            'dapat', 'dsb', 'dst', 'dll', 'dahulu', 'dulunya', 'anu', 'demikian', 'tapi', 'ingin',
            'juga', 'nggak', 'mari', 'nanti', 'melainkan', 'oh', 'ok', 'seharusnya', 'sebetulnya',
            'setiap', 'setidaknya', 'sesuatu', 'pasti', 'saja', 'toh', 'ya', 'walau', 'tolong',
            'tentu', 'amat', 'apalagi', 'bagaimanapun', 'ðŸ’ƒ','ðŸ‡','±','â˜…R', '&am,¢Å,’',
            'â€',"â¤","ðŸ…±â„¢","ROe","ðŸ‘ï¸ðŸ‘ï¸",'²'
            ,'Ã«','','ðŸ”µ','Â','â„¢',"â™¡UVâ™¥",'ã‚¹ãƒ©ãƒžãƒƒãƒ³','ðŸ¤','´ª','ðŸ’','Ø­Ø¨ Ø§Ù„Ù‡Ø¬Ø±Ø©','ˆ®',
            'ðŸŒ´ðŸŒ²ðŸŒ¾','ðŸ˜','â™‰','Š','ÕÑ–Táµ˜áµáµƒâ¿áµï¼§ï½…ï½‚ï½•ï½‹','œ','â¤â¤','ƒ',
            '&gt','_&lt','Â®','â˜…â˜','ðŸ™ˆ','©','â˜','Ù©','ðŸ’','–ðŸ’','ðŸ‡²ðŸ‡¨','ã…¤','ðŸ‡²ðŸ‡´ðŸ‡¸ðŸ‡±ðŸ‡ªðŸ‡²','ðŸ˜Ž','ï£¿',
        );
    }
}
