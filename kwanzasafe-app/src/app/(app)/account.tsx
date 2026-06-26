import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, Pressable, StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { fetchMe } from '@/api/auth';
import { apiErrorMessage } from '@/api/client';
import { updateProfile, uploadAvatar } from '@/api/profile';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { capturePhoto, pickPhoto } from '@/lib/imagePicker';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { PickedFile } from '@/types/api';

/** Edição dos dados pessoais: foto, nome, email + zona de perigo. */
export default function AccountScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const storeUser = useAuthStore((s) => s.user);
  const setUser = useAuthStore((s) => s.setUser);
  const token = useAuthStore((s) => s.token);

  const { data: me } = useQuery({ queryKey: ['me'], queryFn: async () => (await fetchMe()).user });
  const user = me ?? storeUser;

  const [photo, setPhoto] = useState<string | null>(null);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [msg, setMsg] = useState<string | null>(null);
  const [err, setErr] = useState<string | null>(null);
  const [editing, setEditing] = useState(false);

  useEffect(() => {
    if (user) {
      setName(user.full_name ?? '');
      setEmail(user.email ?? '');
    }
  }, [user?.id]); // eslint-disable-line react-hooks/exhaustive-deps

  const profileM = useMutation({
    mutationFn: () => updateProfile({ name: name.trim(), email: email.trim() }),
    onSuccess: (updated) => {
      setErr(null);
      setEditing(false);
      setUser(updated);
      void queryClient.invalidateQueries({ queryKey: ['me'] });
      setMsg(updated.email_verified ? 'Perfil atualizado.' : 'Perfil atualizado. Confirma o novo email.');
    },
    onError: (e) => {
      setMsg(null);
      setErr(apiErrorMessage(e));
    },
  });

  const avatarM = useMutation({
    mutationFn: (f: PickedFile) => uploadAvatar(f),
    onSuccess: (updated) => {
      setUser(updated);
      void queryClient.invalidateQueries({ queryKey: ['me'] });
      setMsg('Foto de perfil atualizada.');
    },
    onError: (e) => {
      setPhoto(null);
      Alert.alert('Foto de perfil', apiErrorMessage(e));
    },
  });

  const handlePicked = (f: PickedFile | null) => {
    if (!f) return;
    setPhoto(f.uri); // pré-visualização otimista
    avatarM.mutate(f);
  };

  const changePhoto = () => {
    setMsg(null);
    Alert.alert('Foto de perfil', 'Escolhe a origem da imagem', [
      {
        text: 'Câmara',
        onPress: async () => {
          try {
            handlePicked(await capturePhoto());
          } catch (e) {
            Alert.alert('Câmara', apiErrorMessage(e));
          }
        },
      },
      {
        text: 'Galeria',
        onPress: async () => {
          try {
            handlePicked(await pickPhoto());
          } catch (e) {
            Alert.alert('Galeria', apiErrorMessage(e));
          }
        },
      },
      { text: 'Cancelar', style: 'cancel' },
    ]);
  };

  const startEdit = () => {
    setMsg(null);
    setErr(null);
    setEditing(true);
  };

  const cancelEdit = () => {
    setName(user?.full_name ?? '');
    setEmail(user?.email ?? '');
    setErr(null);
    setEditing(false);
  };

  const save = () => {
    setErr(null);
    setMsg(null);
    if (!name.trim() || !email.trim()) {
      setErr('Preenche o nome e o email.');
      return;
    }
    profileM.mutate();
  };

  const initial = (name.trim()?.[0] ?? user?.full_name?.trim()?.[0] ?? 'K').toUpperCase();
  const avatarUri = photo ?? user?.avatar_url ?? null;
  const remoteAvatar = !photo && !!user?.avatar_url;
  const authHeaders = token ? { Authorization: `Bearer ${token}` } : undefined;

  return (
    <Screen edges={['top']}>
      <Header title="Dados pessoais" onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} bottomOffset={24}>
          {/* Foto */}
          <View style={styles.photoBlock}>
            <Pressable onPress={changePhoto} style={styles.avatar}>
              {avatarUri ? (
                <Image
                  source={{ uri: avatarUri, headers: remoteAvatar ? authHeaders : undefined }}
                  style={styles.avatarImg}
                  contentFit="cover"
                />
              ) : (
                <Text style={styles.avatarText}>{initial}</Text>
              )}
              {avatarM.isPending && (
                <View style={styles.avatarOverlay}>
                  <ActivityIndicator color={colors.white} />
                </View>
              )}
              <View style={styles.avatarEdit}>
                <Ionicons name="camera" size={14} color={colors.white} />
              </View>
            </Pressable>
            <Pressable onPress={changePhoto} disabled={avatarM.isPending}>
              <Text style={styles.changePhoto}>{avatarM.isPending ? 'A enviar…' : 'Alterar foto'}</Text>
            </Pressable>
          </View>

          {/* Dados */}
          <View style={styles.card}>
            <View style={styles.cardHead}>
              <Text style={styles.cardTitle}>Os teus dados</Text>
              {!editing && <Ionicons name="lock-closed" size={14} color={colors.textMuted} />}
            </View>
            <TextField
              label="Nome completo"
              value={name}
              onChangeText={setName}
              placeholder="O teu nome"
              editable={editing}
              style={!editing ? styles.lockedInput : undefined}
            />
            <TextField
              label="Email"
              value={user?.email ?? ''}
              editable={false}
              style={styles.lockedInput}
            />
            <Pressable onPress={() => router.push('/change-email')} style={styles.linkRow}>
              <Ionicons name="mail-outline" size={16} color={colors.primaryBright} />
              <Text style={styles.link}>Alterar email (com confirmação)</Text>
            </Pressable>
            {!!msg && <Text style={styles.ok}>{msg}</Text>}
            {!!err && <Text style={styles.error}>{err}</Text>}
            {editing ? (
              <View style={styles.editRow}>
                <Button label="Cancelar" variant="ghost" onPress={cancelEdit} style={styles.flex1} />
                <Button label="Guardar" onPress={save} loading={profileM.isPending} style={styles.flex1} />
              </View>
            ) : (
              <Button label="Alterar dados" variant="ghost" onPress={startEdit} />
            )}
          </View>

          <Text style={styles.note}>
            Para eliminar a conta, contacta o suporte em geral@kwanzasafe.com. Por segurança, a eliminação é
            tratada fora da app.
          </Text>
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  scroll: { gap: spacing.lg, paddingVertical: spacing.lg },
  photoBlock: { alignItems: 'center', gap: spacing.sm },
  avatar: {
    width: 96,
    height: 96,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    borderWidth: 1,
    borderColor: colors.primaryTintBorder,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'visible',
  },
  avatarImg: { width: 96, height: 96, borderRadius: radius.pill },
  avatarOverlay: {
    ...StyleSheet.absoluteFillObject,
    borderRadius: radius.pill,
    backgroundColor: 'rgba(0,0,0,0.45)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: { fontFamily: fonts.display, fontSize: 40, color: colors.primaryBright },
  avatarEdit: {
    position: 'absolute',
    right: 2,
    bottom: 2,
    width: 30,
    height: 30,
    borderRadius: radius.pill,
    backgroundColor: colors.primary,
    borderWidth: 3,
    borderColor: colors.bg,
    alignItems: 'center',
    justifyContent: 'center',
  },
  changePhoto: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.primaryBright },
  photoHint: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, textAlign: 'center' },
  card: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.md,
  },
  ok: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.primaryBright },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
  cardHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  cardTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.text },
  lockedInput: { opacity: 0.55 },
  linkRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  link: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.primaryBright },
  editRow: { flexDirection: 'row', gap: spacing.sm },
  flex1: { flex: 1 },
  note: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, lineHeight: 18, paddingHorizontal: spacing.xs },
});
