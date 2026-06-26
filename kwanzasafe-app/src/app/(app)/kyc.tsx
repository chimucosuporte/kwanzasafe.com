import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { fetchMe } from '@/api/auth';
import { apiErrorMessage, apiFieldErrors } from '@/api/client';
import {
  sendPhoneCode,
  submitPersonalData,
  uploadKycDocument,
  uploadKycPhoto,
  verifyPhoneCode,
} from '@/api/kyc';
import { Button } from '@/components/Button';
import { DateField } from '@/components/DateField';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { capturePhoto, pickPhoto } from '@/lib/imagePicker';
import { pickAttachment } from '@/lib/picker';
import { useAuthStore } from '@/stores/auth';
import { toast } from '@/stores/toast';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { KycResponse, PickedFile } from '@/types/api';

const STEPS = ['Dados', 'Telefone', 'Documento', 'Selfie'];

export default function KycScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const setUser = useAuthStore((s) => s.setUser);

  const { data: me } = useQuery({ queryKey: ['me'], queryFn: async () => (await fetchMe()).user });
  const thisYear = new Date().getFullYear();

  const [step, setStep] = useState(1); // 1..4 (5 = concluído)
  const [error, setError] = useState<string | null>(null);
  const [fe, setFe] = useState<Record<string, string>>({}); // erros por campo
  const [finalResult, setFinalResult] = useState<KycResponse | null>(null);

  /** Atualiza um campo e limpa o seu erro inline (e o toast). */
  const clearFe = (field: string) =>
    setFe((prev) => {
      if (!prev[field]) return prev;
      toast.hide();
      return { ...prev, [field]: '' };
    });

  // Passo 1 — dados pessoais
  const [fullName, setFullName] = useState('');
  const [birthDate, setBirthDate] = useState('');
  const [gender, setGender] = useState<'M' | 'F' | null>(null);
  const [biNumber, setBiNumber] = useState('');
  const [biExpiry, setBiExpiry] = useState('');
  const [province, setProvince] = useState('');
  const [municipality, setMunicipality] = useState('');
  const [address, setAddress] = useState('');

  // Passo 2 — telefone
  const [phone, setPhone] = useState('');
  const [phoneSent, setPhoneSent] = useState(false);
  const [phoneOtp, setPhoneOtp] = useState('');
  const [phoneMsg, setPhoneMsg] = useState<string | null>(null);

  // Passos 3/4 — ficheiros
  const [docFile, setDocFile] = useState<PickedFile | null>(null);
  const [selfieFile, setSelfieFile] = useState<PickedFile | null>(null);

  useEffect(() => {
    if (me) {
      if (!fullName) setFullName(me.full_name ?? '');
      if (!province) setProvince(me.province ?? '');
      if (!phone) setPhone(me.phone_number ?? '');
    }
  }, [me?.id]); // eslint-disable-line react-hooks/exhaustive-deps

  const afterKyc = (res: KycResponse, next: number) => {
    setError(null);
    setFe({});
    setUser(res.user);
    void queryClient.invalidateQueries({ queryKey: ['me'] });
    if (next > 4) setFinalResult(res);
    setStep(next);
  };

  const personalM = useMutation({
    mutationFn: () =>
      submitPersonalData({
        full_name: fullName.trim(),
        birth_date: birthDate.trim(),
        gender: gender ?? 'M',
        bi_number: biNumber.trim(),
        bi_expiry: biExpiry.trim(),
        province: province.trim(),
        municipality: municipality.trim(),
        address: address.trim(),
      }),
    onSuccess: (res) => afterKyc(res, 2),
    onError: (e) => {
      const f = apiFieldErrors(e);
      setFe(f);
      if (Object.keys(f).length) {
        setError(null);
        toast.error('Corrige os campos destacados abaixo.');
      } else {
        const m = apiErrorMessage(e);
        setError(m);
        toast.error(m);
      }
    },
  });

  const sendPhoneM = useMutation({
    mutationFn: () => sendPhoneCode(phone.trim()),
    onSuccess: ({ message }) => {
      setError(null);
      setFe({});
      setPhoneSent(true);
      setPhoneMsg(message);
    },
    onError: (e) => {
      const f = apiFieldErrors(e);
      setFe(f);
      setError(Object.keys(f).length ? null : apiErrorMessage(e));
    },
  });

  const verifyPhoneM = useMutation({
    mutationFn: () => verifyPhoneCode(phoneOtp.trim()),
    onSuccess: (res) => afterKyc(res, 3),
    onError: (e) => {
      const f = apiFieldErrors(e);
      // o backend valida 'phone_otp'
      setFe(f);
      setError(Object.keys(f).length ? null : apiErrorMessage(e));
    },
  });

  const docM = useMutation({
    mutationFn: () => uploadKycDocument(docFile!),
    onSuccess: (res) => afterKyc(res, 4),
    onError: (e) => setError(apiErrorMessage(e)),
  });

  const photoM = useMutation({
    mutationFn: () => uploadKycPhoto(selfieFile!),
    onSuccess: (res) => afterKyc(res, 5),
    onError: (e) => setError(apiErrorMessage(e)),
  });

  const pick = async (source: 'camera' | 'file', forSelfie: boolean) => {
    setError(null);
    try {
      const file =
        source === 'camera' ? await capturePhoto() : forSelfie ? await pickPhoto() : await pickAttachment();
      if (file) (forSelfie ? setSelfieFile : setDocFile)(file);
    } catch (e) {
      setError(apiErrorMessage(e));
    }
  };

  const submitPersonal = () => {
    setError(null);
    const errs: Record<string, string> = {};
    if (!fullName.trim()) errs.full_name = 'Indica o teu nome completo (como no BI).';
    if (!birthDate.trim()) errs.birth_date = 'Indica a data de nascimento (AAAA-MM-DD).';
    if (!gender) errs.gender = 'Seleciona o género.';
    if (!biNumber.trim()) errs.bi_number = 'Indica o número do BI.';
    if (!biExpiry.trim()) errs.bi_expiry = 'Indica a validade do BI (AAAA-MM-DD).';
    if (!province.trim()) errs.province = 'Indica a província.';
    if (!municipality.trim()) errs.municipality = 'Indica o município.';
    if (!address.trim()) errs.address = 'Indica a morada.';
    setFe(errs);
    if (Object.keys(errs).length) {
      toast.error('Há campos por preencher. Verifica os destacados abaixo.');
      return;
    }
    personalM.mutate();
  };

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Verificação (KYC)" onBack={() => router.back()} />

      {step <= 4 && <Stepper step={step} />}

      <KeyboardAwareScrollView
        contentContainerStyle={styles.scroll}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        bottomOffset={24}
      >
          {!!error && <Text style={styles.error}>{error}</Text>}

          {/* PASSO 1 — DADOS PESSOAIS */}
          {step === 1 && (
            <View style={styles.section}>
              <Text style={styles.title}>Dados pessoais</Text>
              <Text style={styles.help}>Usa os dados exatamente como aparecem no teu Bilhete de Identidade.</Text>
              <TextField label="Nome completo (como no BI)" value={fullName} onChangeText={(t) => { setFullName(t); clearFe('full_name'); }} error={fe.full_name} />
              <DateField
                label="Data de nascimento"
                value={birthDate}
                onChange={(v) => { setBirthDate(v); clearFe('birth_date'); }}
                error={fe.birth_date}
                minYear={thisYear - 100}
                maxYear={thisYear - 18}
                yearOrder="desc"
              />
              <View>
                <Text style={styles.fieldLabel}>Género</Text>
                <View style={styles.chips}>
                  {(['M', 'F'] as const).map((g) => (
                    <Pressable key={g} onPress={() => { setGender(g); clearFe('gender'); }} style={[styles.chip, gender === g && styles.chipActive]}>
                      <Text style={[styles.chipText, gender === g && styles.chipTextActive]}>{g === 'M' ? 'Masculino' : 'Feminino'}</Text>
                    </Pressable>
                  ))}
                </View>
                {!!fe.gender && <Text style={styles.fieldError}>{fe.gender}</Text>}
              </View>
              <TextField label="Número do BI" value={biNumber} onChangeText={(t) => { setBiNumber(t); clearFe('bi_number'); }} autoCapitalize="characters" placeholder="000000000XX000" error={fe.bi_number} />
              <DateField
                label="Validade do BI"
                value={biExpiry}
                onChange={(v) => { setBiExpiry(v); clearFe('bi_expiry'); }}
                error={fe.bi_expiry}
                minYear={thisYear}
                maxYear={thisYear + 20}
                yearOrder="asc"
              />
              <TextField label="Província" value={province} onChangeText={(t) => { setProvince(t); clearFe('province'); }} error={fe.province} />
              <TextField label="Município" value={municipality} onChangeText={(t) => { setMunicipality(t); clearFe('municipality'); }} error={fe.municipality} />
              <TextField label="Morada" value={address} onChangeText={(t) => { setAddress(t); clearFe('address'); }} error={fe.address} />
              <Button label="Continuar" onPress={submitPersonal} loading={personalM.isPending} />
            </View>
          )}

          {/* PASSO 2 — TELEFONE */}
          {step === 2 && (
            <View style={styles.section}>
              <Text style={styles.title}>Confirmar telefone</Text>
              <Text style={styles.help}>Enviamos um código por email para confirmar o número.</Text>
              <TextField label="Número de telefone" value={phone} onChangeText={(t) => { setPhone(t); clearFe('phone'); }} keyboardType="phone-pad" placeholder="+244 9XX XXX XXX" editable={!phoneSent} error={fe.phone} />
              {!phoneSent ? (
                <Button label="Enviar código" onPress={() => sendPhoneM.mutate()} loading={sendPhoneM.isPending} />
              ) : (
                <>
                  {!!phoneMsg && <Text style={styles.ok}>{phoneMsg}</Text>}
                  <TextField label="Código (6 dígitos)" value={phoneOtp} onChangeText={(t) => { setPhoneOtp(t.replace(/[^0-9]/g, '').slice(0, 6)); clearFe('phone_otp'); }} keyboardType="number-pad" placeholder="000000" error={fe.phone_otp} />
                  <Button label="Confirmar telefone" onPress={() => verifyPhoneM.mutate()} loading={verifyPhoneM.isPending} />
                  <Button label="Reenviar código" variant="ghost" onPress={() => sendPhoneM.mutate()} loading={sendPhoneM.isPending} />
                </>
              )}
            </View>
          )}

          {/* PASSO 3 — DOCUMENTO */}
          {step === 3 && (
            <View style={styles.section}>
              <Text style={styles.title}>Documento de identidade</Text>
              <Text style={styles.help}>Fotografa a frente do teu BI. Garante que está nítido e que todos os cantos aparecem.</Text>

              <DocFrame file={docFile} />

              <View style={styles.pickRow}>
                <Button label="Fotografar" onPress={() => pick('camera', false)} style={styles.pickBtn} />
                <Button label="Anexar ficheiro" variant="ghost" onPress={() => pick('file', false)} style={styles.pickBtn} />
              </View>
              <Button label="Enviar documento" onPress={() => docM.mutate()} loading={docM.isPending} disabled={!docFile} />
            </View>
          )}

          {/* PASSO 4 — SELFIE / LIVENESS */}
          {step === 4 && (
            <View style={styles.section}>
              <Text style={styles.title}>Selfie de verificação</Text>
              <Text style={styles.help}>Centra o rosto no círculo, com boa luz, sem óculos escuros nem chapéu.</Text>

              <SelfieFrame file={selfieFile} />

              {/* Auditoria automática humano vs robô */}
              <View style={styles.liveCard}>
                <View style={styles.liveIcon}>
                  <Ionicons name="scan-circle" size={22} color={colors.primaryBright} />
                </View>
                <View style={styles.flex}>
                  <Text style={styles.liveTitle}>Verificação humano vs robô</Text>
                  <Text style={styles.liveText}>
                    O nosso sistema analisa automaticamente a tua selfie para confirmar que é uma pessoa real
                    e que coincide com o documento.
                  </Text>
                </View>
              </View>

              <View style={styles.pickRow}>
                <Button label="Tirar selfie" onPress={() => pick('camera', true)} style={styles.pickBtn} />
                <Button label="Galeria" variant="ghost" onPress={() => pick('file', true)} style={styles.pickBtn} />
              </View>
              <Button label="Enviar e concluir" onPress={() => photoM.mutate()} loading={photoM.isPending} disabled={!selfieFile} />
            </View>
          )}

          {/* CONCLUÍDO */}
          {step === 5 && finalResult && <FinalCard result={finalResult} onClose={() => router.back()} onRestart={() => { setFinalResult(null); setDocFile(null); setSelfieFile(null); setStep(1); }} />}
      </KeyboardAwareScrollView>
    </Screen>
  );
}

/** Indicador de progresso com linha de ligação. */
function Stepper({ step }: { step: number }) {
  return (
    <View style={styles.progress}>
      {STEPS.map((label, i) => {
        const n = i + 1;
        const state = n < step ? 'done' : n === step ? 'active' : 'todo';
        return (
          <View key={label} style={styles.progressItem}>
            {i > 0 && <View style={[styles.line, n <= step && styles.lineOn]} />}
            <View style={[styles.bullet, state === 'active' && styles.bulletActive, state === 'done' && styles.bulletDone]}>
              {state === 'done' ? (
                <Ionicons name="checkmark" size={16} color={colors.white} />
              ) : (
                <Text style={[styles.bulletText, state === 'active' && styles.bulletTextOn]}>{n}</Text>
              )}
            </View>
            <Text style={[styles.stepLabel, state === 'active' && styles.stepLabelActive]}>{label}</Text>
          </View>
        );
      })}
    </View>
  );
}

/** Moldura de captura do documento (cartão com cantos). */
function DocFrame({ file }: { file: PickedFile | null }) {
  const isImage = file?.mimeType.startsWith('image/');
  return (
    <View style={styles.docFrame}>
      {file && isImage ? (
        <Image source={{ uri: file.uri }} style={styles.docImage} contentFit="cover" />
      ) : file ? (
        <View style={styles.docDoc}>
          <Ionicons name="document-text" size={40} color={colors.primaryBright} />
          <Text style={styles.docName} numberOfLines={1}>{file.name}</Text>
        </View>
      ) : (
        <>
          <View style={[styles.corner, styles.cTL]} />
          <View style={[styles.corner, styles.cTR]} />
          <View style={[styles.corner, styles.cBL]} />
          <View style={[styles.corner, styles.cBR]} />
          <Ionicons name="card-outline" size={48} color={colors.textFaint} />
          <Text style={styles.frameHint}>Enquadra o BI aqui</Text>
        </>
      )}
    </View>
  );
}

/** Moldura circular para a selfie. */
function SelfieFrame({ file }: { file: PickedFile | null }) {
  const isImage = file?.mimeType.startsWith('image/');
  return (
    <View style={styles.selfieWrap}>
      <View style={styles.selfieRing}>
        {file && isImage ? (
          <Image source={{ uri: file.uri }} style={styles.selfieImage} contentFit="cover" />
        ) : (
          <Ionicons name="person-outline" size={72} color={colors.textFaint} />
        )}
      </View>
      {file && isImage && (
        <View style={styles.selfieCheck}>
          <Ionicons name="checkmark-circle" size={28} color={colors.primaryBright} />
        </View>
      )}
    </View>
  );
}

/** Cartão de resultado final com score do bot. */
function FinalCard({ result, onClose, onRestart }: { result: KycResponse; onClose: () => void; onRestart: () => void }) {
  const { status, score } = result.kyc;
  const approved = status === 'auto_approved';
  const rejected = status === 'auto_rejected';
  const tone = approved ? colors.primaryBright : rejected ? colors.danger : colors.warning;
  const icon = approved ? 'shield-checkmark' : rejected ? 'alert-circle' : 'time';
  const heading = approved ? 'Verificação aprovada!' : rejected ? 'Precisa de correção' : 'Em revisão';

  return (
    <View style={styles.finalWrap}>
      <View style={[styles.scoreRing, { borderColor: tone }]}>
        <Ionicons name={icon} size={40} color={tone} />
      </View>
      <Text style={styles.title}>{heading}</Text>
      <Text style={styles.help}>{result.message}</Text>
      <View style={styles.scoreRow}>
        <Text style={styles.scoreLabel}>Pontuação de confiança</Text>
        <Text style={[styles.scoreValue, { color: tone }]}>{score ?? 0}/100</Text>
      </View>
      <Button label="Concluir" onPress={onClose} />
      {rejected && <Button label="Recomeçar" variant="ghost" onPress={onRestart} />}
    </View>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },

  progress: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: spacing.md },
  progressItem: { alignItems: 'center', gap: 4, flex: 1 },
  line: { position: 'absolute', top: 15, right: '50%', width: '100%', height: 2, backgroundColor: colors.border },
  lineOn: { backgroundColor: colors.primary },
  bullet: { width: 30, height: 30, borderRadius: 30, backgroundColor: colors.surface, borderWidth: 1.5, borderColor: colors.border, alignItems: 'center', justifyContent: 'center', zIndex: 1 },
  bulletActive: { borderColor: colors.primary, backgroundColor: colors.primaryTint },
  bulletDone: { borderColor: colors.primary, backgroundColor: colors.primary },
  bulletText: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted },
  bulletTextOn: { color: colors.primaryBright },
  stepLabel: { fontFamily: fonts.body, fontSize: 10, color: colors.textMuted },
  stepLabelActive: { color: colors.primaryBright, fontFamily: fonts.bodyBold },

  scroll: { gap: spacing.md, paddingVertical: spacing.md },
  section: { gap: spacing.md },
  title: { fontFamily: fonts.displaySemi, fontSize: fontSize.xl, color: colors.text },
  help: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 20 },
  fieldLabel: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text },
  chips: { flexDirection: 'row', gap: spacing.sm },
  chip: { flex: 1, paddingVertical: spacing.md, borderRadius: radius.md, borderWidth: 1.5, borderColor: colors.border, backgroundColor: colors.surface, alignItems: 'center' },
  chipActive: { borderColor: colors.primary, backgroundColor: colors.primaryTint },
  chipText: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted },
  chipTextActive: { color: colors.primaryBright },
  pickRow: { flexDirection: 'row', gap: spacing.sm },
  pickBtn: { flex: 1 },
  ok: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.primaryBright },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
  fieldError: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.danger, marginTop: 4 },

  // Documento
  docFrame: {
    height: 200,
    borderRadius: radius.lg,
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    gap: spacing.sm,
  },
  docImage: { width: '100%', height: '100%' },
  docDoc: { alignItems: 'center', gap: spacing.sm, paddingHorizontal: spacing.lg },
  docName: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text },
  frameHint: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textFaint },
  corner: { position: 'absolute', width: 28, height: 28, borderColor: colors.primaryBright },
  cTL: { top: 16, left: 16, borderTopWidth: 3, borderLeftWidth: 3, borderTopLeftRadius: 10 },
  cTR: { top: 16, right: 16, borderTopWidth: 3, borderRightWidth: 3, borderTopRightRadius: 10 },
  cBL: { bottom: 16, left: 16, borderBottomWidth: 3, borderLeftWidth: 3, borderBottomLeftRadius: 10 },
  cBR: { bottom: 16, right: 16, borderBottomWidth: 3, borderRightWidth: 3, borderBottomRightRadius: 10 },

  // Selfie
  selfieWrap: { alignItems: 'center', justifyContent: 'center', paddingVertical: spacing.sm },
  selfieRing: {
    width: 180,
    height: 180,
    borderRadius: 90,
    borderWidth: 3,
    borderColor: colors.primaryTintBorder,
    backgroundColor: colors.surface,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  selfieImage: { width: '100%', height: '100%' },
  selfieCheck: { position: 'absolute', bottom: spacing.sm, right: '32%', backgroundColor: colors.bg, borderRadius: radius.pill },

  liveCard: {
    flexDirection: 'row',
    gap: spacing.sm,
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.md,
  },
  liveIcon: { width: 40, height: 40, borderRadius: radius.md, backgroundColor: colors.primaryTint, alignItems: 'center', justifyContent: 'center' },
  liveTitle: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  liveText: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, lineHeight: 17, marginTop: 2 },

  // Final
  finalWrap: { alignItems: 'center', gap: spacing.md, paddingVertical: spacing.lg },
  scoreRing: { width: 96, height: 96, borderRadius: 48, borderWidth: 3, alignItems: 'center', justifyContent: 'center' },
  scoreRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    alignSelf: 'stretch',
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.md,
  },
  scoreLabel: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
  scoreValue: { fontFamily: fonts.display, fontSize: fontSize.lg },
});
